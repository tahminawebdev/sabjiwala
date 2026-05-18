# Docker Compose: production topology — design

**Status:** draft, awaiting review.
**Date:** 2026-05-18.
**Scope:** evolve the dev `docker-compose.yml` into a two-mode stack — `dev` (today's single-replica layout, kept) and `prod` (Nginx load balancer in front of ≥2 WordPress replicas, hourly MariaDB backups with 24-hour rolling retention, permanent volumes for DB / uploads / backups).
**Explicitly out of scope:** fixing the security findings from `2026-05-18-security-review.md`. Those land in a follow-up commit per the "report before fix" instruction.

## 1. Decision summary

1. **One repo, two files.** Keep `docker-compose.yml` (dev, single replica). Add `docker-compose.prod.yml` (override for prod). Run prod as `docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d`. Idiomatic, lets dev stay simple, no profiles maze.
2. **Nginx as L7 load balancer.** New `nginx` service in front; round-robin upstream to `wordpress` service. `docker compose up --scale wordpress=2` (or more) gives N replicas, Docker's embedded DNS resolves the service name to all replica IPs.
3. **Redis for shared state.** New `redis` service. WordPress object cache + PHP sessions move to Redis so any replica can serve any request. Without this, replicas cache-poison each other and users log out on every round-robin.
4. **MariaDB backup sidecar.** New `db-backup` service based on `mariadb:10.11`. Cron at `0 * * * *` runs `mysqldump | gzip` into a named volume. A `find … -mmin +1440 -delete` step prunes anything older than 24 hours each run, so retention sits at exactly 24 files.
5. **Permanent volumes.** Three named volumes survive `docker compose down`: `db_data` (already exists), `wp_uploads` (new — bind mount stays in dev), `db_backups` (new). `down -v` is still the documented reset path; warn in README.
6. **phpMyAdmin removed from prod.** Not exposed publicly. Dev keeps it.

## 2. Topology

```
                          ┌────────────────────────────┐
   public :443 ───────────►│  nginx  (load balancer +   │
                          │         TLS termination)   │
                          └────┬───────────────────────┘
                               │ http upstream
              ┌────────────────┼─────────────────┐
              ▼                ▼                 ▼
       ┌────────────┐   ┌────────────┐    ┌────────────┐
       │ wordpress  │   │ wordpress  │    │  ...       │  (N replicas via --scale)
       │ (apache+php│   │ (apache+php│    │            │
       │  on :80)   │   │  on :80)   │    │            │
       └─────┬──────┘   └─────┬──────┘    └─────┬──────┘
             │                │                 │
             ▼                ▼                 ▼
       ┌────────────────────────────────────────────────┐
       │              redis   (object cache + sessions) │
       └────────────────────────────────────────────────┘
       ┌────────────────────────────────────────────────┐
       │              mariadb  (single instance)        │
       └────┬───────────────────────────────────────────┘
            │
            ▼
       ┌────────────┐   hourly mysqldump.gz, 24-file rolling retention
       │ db-backup  │───────────────────────────────────►  db_backups (volume)
       │ (cron)     │
       └────────────┘
```

## 3. File plan

```
docker-compose.yml            # dev — single WP, single DB, phpMyAdmin
docker-compose.prod.yml       # prod overlay — adds nginx, redis, db-backup;
                              # removes phpMyAdmin; removes the dev WP bind
                              # mounts in favour of a named volume for uploads
docker/nginx/nginx.conf       # upstream block, gzip, brotli, sensible timeouts
docker/nginx/conf.d/site.conf # server block, HTTP→HTTPS, X-Forwarded-* passthrough
docker/db-backup/Dockerfile   # mariadb:10.11 + cron + tini
docker/db-backup/backup.sh    # mysqldump | gzip; prune files older than 24h
docker/db-backup/crontab      # 0 * * * * /usr/local/bin/backup.sh
.env.prod.example             # prod-only env: REDIS_PASSWORD, NGINX_SSL_*, etc.
```

The `docker compose` invocation pattern stays consistent:

```bash
# dev (unchanged)
docker compose up -d

# prod (new)
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --scale wordpress=2
```

## 4. Component decisions

### 4.1 Load balancer choice — Nginx, not Traefik / Caddy / HAProxy

- **Nginx**: small, mature, every dev recognises it, manual config is unambiguous. Picked.
- **Traefik**: automatic via labels, beautiful for dynamic envs — overkill for a fixed-scale WooCommerce site.
- **Caddy**: lovely auto-TLS but smaller ecosystem and less common in WordPress hosting.
- **HAProxy**: L4 strength wasted here; we need L7 (host headers, X-Forwarded-Proto for the Apple Pay HTTPS detection in `wp-config.php:65-67`).

### 4.2 Sticky sessions — no, use Redis instead

Sticky sessions (`ip_hash` in Nginx) work for PHP sessions but **not** for WordPress's object cache or transients, which sit in memory per-replica. Without a shared cache, replicas serve stale data after option updates. Redis-backed object cache (`wp-redis` or `redis-object-cache` plugin from wpackagist) fixes both at once.

### 4.3 TLS termination — at Nginx

Two options:

- **A. Terminate at Nginx (picked).** Nginx holds the cert, talks plain HTTP upstream to WordPress. Sets `X-Forwarded-Proto: https`. `wp-config.php` already honours this header (lines 65-67) so WP generates HTTPS URLs correctly.
- **B. Pass-through to an upstream LB (Cloudflare, AWS ALB).** Skip the Nginx cert entirely; let Cloudflare proxy. Compose stack stays HTTP-only inside.

This spec implements A. Switching to B later is a config trim, not a rewrite.

Cert source: Let's Encrypt via a `certbot` sidecar. Cert lives in a named volume `nginx_certs` so it survives restarts. For staging, mkcert local certs work — see `.env.prod.example`.

### 4.4 Uploads — Docker named volume, shared between replicas

Sufficient for a single-host deployment. WordPress writes to `/var/www/html/wp-content/uploads`; all replicas mount the same `wp_uploads` named volume there. Multi-host scaling later requires either an NFS / EFS / Cloud Filestore mount or an offload plugin (`humanmade/s3-uploads`), tracked as a follow-up — not day-one.

### 4.5 Backups — `mysqldump`, not `mariabackup`

| Tool | Pros | Cons |
| --- | --- | --- |
| `mysqldump` (picked) | Logical backup, portable, restores to any version, smaller archives | Locks tables (briefly) |
| `mariabackup` | Hot physical backup, no locking | Restore needs same-version MariaDB, larger archives |

For an hourly cadence on a moderately sized DB (single-digit GB), `mysqldump --single-transaction --quick` is fast enough and trivially restorable to any host.

Retention: keep exactly the last **24** files. Backup script runs `find /backups -name 'backup-*.sql.gz' -mmin +1440 -delete` after each dump.

File pattern: `backup-2026-05-18T14-00.sql.gz` (ISO timestamps, hyphens for filesystem safety).

### 4.6 Volume permanence

All persistent state lives in **named** Docker volumes, not bind mounts:

| Volume | Lifecycle | Holds |
| --- | --- | --- |
| `db_data` | already exists | MariaDB data files. Survives `down`, killed by `down -v`. |
| `db_backups` (new) | survives `down` | Hourly mysqldumps. |
| `wp_uploads` (new in prod) | survives `down` | `wp-content/uploads`. Dev keeps bind mount for fast iteration. |
| `nginx_certs` (new in prod) | survives `down` | Let's Encrypt cert + private key. |
| `redis_data` (new in prod) | survives `down` | Redis RDB snapshot for warm-start cache. |

Dev bind mounts for `wp-content/themes`, `wp-content/plugins`, `wp-content/mu-plugins` remain — they need to be readable from the host for editing. Prod replaces them with named volumes seeded from the image; deploys ship code by rebuilding the image, not by mounting host paths.

## 5. WordPress configuration changes

These belong with the implementation, listed here so the design is complete:

| Concern | Change |
| --- | --- |
| Redis object cache | Add `wpackagist-plugin/redis-cache` to `composer.json`. Activate in prod. In `wp-config.php`, set `WP_REDIS_HOST` (env), `WP_CACHE_KEY_SALT` (env, unique per env). |
| Sessions on Redis | PHP `session.save_handler=redis`, `session.save_path=tcp://redis:6379` via a php.ini fragment in the prod Dockerfile overlay. |
| HTTPS detection | Already handled in `wp-config.php:65-67`. Verify Nginx sets `X-Forwarded-Proto: https`. |
| `WP_HOME` / `WP_SITEURL` | Prod env points to the real domain. |
| `WP_DEBUG` | Default to `false` in `.env.prod.example`. |

## 6. Operational notes

- `docker compose -f docker-compose.yml -f docker-compose.prod.yml ps` shows all services.
- `docker compose -f ... -f ... logs -f nginx wordpress` to follow LB and app logs.
- Backup restoration: `gunzip < db_backups/backup-…sql.gz | docker compose exec -T db mysql -uroot -p"$DB_ROOT_PASSWORD" "$DB_NAME"`.
- Scale up/down: `docker compose -f ... -f ... up -d --scale wordpress=4`. Nginx upstream auto-resolves via Docker DNS (refresh interval ~10s).
- Rolling deploys: `docker compose ... up -d --no-deps --build wordpress` followed by `--scale` to drain old containers.

## 7. Forks I want explicit sign-off on

| # | Choice | My pick | Alternative |
| --- | --- | --- | --- |
| F1 | Compose file layout | Two files (`docker-compose.yml` + `docker-compose.prod.yml`). | One file with profiles `dev` / `prod`. |
| F2 | LB software | Nginx. | Traefik (label-driven). |
| F3 | TLS | Terminate at Nginx with Let's Encrypt sidecar. | Assume upstream LB (Cloudflare). |
| F4 | Shared state | Redis (object cache + sessions). | Sticky sessions + accept cache staleness. |
| F5 | Uploads | Shared named volume (single host). | S3 offload plugin (multi-host ready). |
| F6 | Backup tool | `mysqldump` hourly. | `mariabackup` hourly. |
| F7 | Backup retention | 24 files (24 hours). | Could extend to 7×daily + 24×hourly. |

If any of these picks is wrong, redirect; otherwise I'll implement F1–F7 as above.

## 8. Non-goals (do-later)

- **Monitoring**: Prometheus / Loki / Grafana. Adds 3+ services; treat as separate spec.
- **Off-host backup**: send `db_backups/*` to S3 via a `restic` or `rclone` sidecar. Trivial to add once the local backup story is proven.
- **WAF / IDS**: Cloudflare, fail2ban, ModSecurity. Trivial to add at the Nginx layer.
- **Blue/green deploys**: doable with two compose stacks behind a swing DNS record. Not now.
- **Object cache warm-up**: cron to hit the home page after a restart so the first real visitor doesn't pay the cache-miss cost.
- **Security fixes from the 2026-05-18 review** — handled in their own commits after you sign off on the findings.

## 9. Spec self-review

- No placeholders, no TBD.
- Internal consistency: §2 topology matches §3 file plan matches §4 component decisions.
- Scope: one implementation pass, ~8 new files, ~150 lines of new compose YAML. Single PR.
- Ambiguity: the seven forks in §7 are the only open questions. Everything else is locked.
