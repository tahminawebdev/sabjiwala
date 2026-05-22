# Deployment design — GitHub Actions → `157.245.45.208`

**Date:** 2026-05-22
**Status:** Draft, ready for review
**Author:** Pair-designed with Claude

## 1. Goal

Set up a manually-triggered GitHub Actions pipeline that deploys this composer-managed WordPress stack to `root@157.245.45.208` (a fresh DigitalOcean Ubuntu 24.04 droplet, 2 vCPU / 4 GB RAM / 120 GB, LON1), pushes the local development database, fixes the site URL, and hardens the server.

**Constraint noted:** the production server will be swapped out soon. The pipeline must be portable — every server-specific value lives in workflow inputs / secrets, never hardcoded.

## 2. Non-goals

- Continuous deployment on push (everything is manual via `workflow_dispatch`).
- TLS / Let's Encrypt — deferred until a real domain is pointed at the IP.
- Multi-server / multi-environment (staging vs prod) — single prod environment for now.
- DB migrations between schema versions — this is a WordPress site, plugins handle their own.

## 3. Architecture overview

```
┌──────────────────────────────────────────────────────────────────┐
│  GitHub Actions (workflow_dispatch — all manual)                  │
│                                                                    │
│   1. server-bootstrap.yml    (run ONCE, when you flip to root key)│
│   2. deploy.yml              (run on every release)               │
│   3. db-push.yml             (run on first deploy + when needed)  │
│   4. rollback.yml            (run when a deploy goes sideways)    │
└──────────────────────────────────────────────────────────────────┘
                                │  SSH (deploy user, key in GH Secrets)
                                ▼
┌──────────────────────────────────────────────────────────────────┐
│  157.245.45.208 — Ubuntu 24.04 Docker host                        │
│                                                                    │
│   /opt/shobjiwala/                                                 │
│     current  ───────► releases/2026-05-22T14-30-00Z/              │
│     releases/                                                      │
│       2026-05-22T14-30-00Z/   ← rsync target, atomic switch        │
│       2026-05-21T09-15-00Z/   ← prior release (rollback target)   │
│       …                       (keep last 5, prune older)          │
│     shared/                                                        │
│       .env                    ← prod secrets, never in git         │
│       uploads/                ← named volume mount source          │
│                                                                    │
│   docker compose (-f docker-compose.yml -f docker-compose.prod.yml)│
│                                                                    │
│     ┌────────┐   ┌─────────┐   ┌────────────┐                     │
│     │ nginx  │──►│ wp x N  │──►│ mariadb    │                     │
│     │ :80    │   │ apache  │   └────────────┘                     │
│     └────────┘   └─────────┘   ┌────────────┐                     │
│                                 │ redis      │                     │
│                                 └────────────┘                     │
│                                 ┌────────────┐                     │
│                                 │ db-backup  │ (hourly mysqldump) │
│                                 └────────────┘                     │
└──────────────────────────────────────────────────────────────────┘
```

The Nginx + Apache layering is already present in `docker-compose.prod.yml` (nginx is L7 LB in front of N WordPress Apache+PHP replicas). This design does **not** modify the compose stack — only adds a single `/healthz` location to `docker/nginx/conf.d/site.conf` for deploy health-checks.

## 4. Deployment-strategy decision

Three options were considered for getting code onto the server:

|                     | A. Server pulls git + runs composer | **B. CI rsyncs files (composer in CI)** | C. CI builds Docker image → registry → server pulls |
|---------------------|-------------------------------------|------------------------------------------|------------------------------------------------------|
| Server needs        | Git, PHP, composer, SSH             | **Just Docker + SSH**                    | Just Docker + SSH                                    |
| Build reproducibility | Worst (server-time variance)      | **Good**                                 | Best (immutable image)                               |
| First-deploy speed  | Fast                                | **Fast**                                 | Slow (image push)                                    |
| Rollback            | Manual git checkout                 | **Keep prior `releases/<ts>/` symlink**  | Re-pull prior image tag                              |
| Secrets exposure    | Composer auth on server             | **None on server**                       | Registry creds on server                             |
| Complexity          | Low                                 | **Medium**                               | High                                                 |
| Disk usage on 4 GB droplet | Low                          | **Low**                                  | High (image layers)                                  |

**Chosen: B.** Composer runs in the GitHub runner; rsync ships the resulting `vendor/` + tracked `wp-content/` into a timestamped release directory; an atomic symlink flip switches the live release. C only wins with multiple servers; A puts too much toolchain on the host. B keeps the droplet minimal, makes the build reproducible, gives instant rollback, and avoids registry secrets.

## 5. Components

### 5.1 Server bootstrap — `server-bootstrap.yml` + `deploy/bootstrap.sh`

Runs once with a temporary `root` key that DigitalOcean planted at droplet creation. Idempotent — safe to re-run.

**Workflow inputs:**
- `laptop_ssh_pubkey` — your laptop's public key, planted in `root@`'s authorized_keys as a break-glass route.
- `ci_ssh_pubkey` — the CI key's public half, planted in `deploy@`'s authorized_keys.

**Secrets (GitHub repo):**
- `BOOTSTRAP_SSH_KEY` — private key currently authenticating as `root@$SERVER_HOST`. Used **only** by this workflow, deleted from secrets after first successful run.
- `SERVER_HOST` — IP (today `157.245.45.208`; updated when server changes).

**`bootstrap.sh` steps on the droplet (in order):**

1. `apt update` + `apt full-upgrade -y` + install `unattended-upgrades`.
2. Install: `docker-ce`, `docker-compose-plugin`, `ufw`, `fail2ban`, `rsync`, `curl`.
3. Create `/swapfile` (2 GB) if absent — 4 GB RAM is tight for MariaDB + Redis + 2× WP replicas.
4. Create `deploy` user, add to `docker` group, no sudo.
5. `mkdir -p /opt/shobjiwala/{releases,shared/uploads}`, `chown -R deploy:deploy`.
6. Plant `ci_ssh_pubkey` in `~deploy/.ssh/authorized_keys` (chmod 700/600).
7. Plant `laptop_ssh_pubkey` in `/root/.ssh/authorized_keys`.
8. Verify CI can SSH as `deploy@` **before** hardening (fail-fast lockout protection).
9. Harden `/etc/ssh/sshd_config`:
   ```
   PasswordAuthentication no
   PermitRootLogin prohibit-password
   KbdInteractiveAuthentication no
   ChallengeResponseAuthentication no
   ```
   `systemctl restart ssh`.
10. UFW: `allow 22, 80, 443`; default deny incoming; `ufw enable`.
11. fail2ban: enable `sshd` jail with defaults.
12. `timedatectl set-timezone UTC`.
13. `docker info` — fail fast if Docker isn't healthy.
14. Write `/opt/shobjiwala/.bootstrap-version` so re-runs can detect prior completion.

After successful run, the workflow prints a reminder to delete `BOOTSTRAP_SSH_KEY` from GH Secrets.

### 5.2 Deploy — `deploy.yml` + `deploy/release.sh`

**Trigger:** `workflow_dispatch`. Optional input: `ref` (branch/tag, default `main`).

**Secrets:**
- `SERVER_HOST`
- `SSH_USER` — `deploy`
- `SSH_PRIVATE_KEY` — CI's private key

**CI job steps (ubuntu-latest):**

1. `actions/checkout@v4` at `ref`.
2. `shivammathur/setup-php@v2` with PHP 8.3 + composer v2.
3. `composer install --no-dev --optimize-autoloader --no-interaction`.
4. Stage build artifact dir `build/`:
   - rsync `wp-content/{themes,plugins,mu-plugins}/` → `build/wp-content/`
   - rsync `vendor/` → `build/vendor/`
   - copy `wp-config.php`, `composer.{json,lock}`, `docker/`, `docker-compose*.yml` → `build/`
   - exclude: `.git`, `node_modules`, tests, `.env*`, `*.sql`, `data/`, `.organic-ai/`, `wp-content/uploads/`
5. `RELEASE_TS=$(date -u +%Y-%m-%dT%H-%M-%SZ)`.
6. Add SSH key to agent + `ssh-keyscan $SERVER_HOST` into known_hosts.
7. `rsync -az --delete build/ deploy@$SERVER_HOST:/opt/shobjiwala/releases/$RELEASE_TS/`.
8. SSH-execute `deploy/release.sh` on the server with `$RELEASE_TS`.
9. Health-check: `curl -fsS http://$SERVER_HOST/healthz` (retry 10× over 60s).
10. On health-check failure → auto-rollback: SSH server to flip symlink to previous release + `docker compose up -d`.

**`deploy/release.sh` (on the server):**

```bash
#!/usr/bin/env bash
set -euo pipefail
RELEASE_TS="$1"
APP_DIR=/opt/shobjiwala
NEW=$APP_DIR/releases/$RELEASE_TS

# Link shared bits into the new release so symlink flip is atomic.
ln -sfn $APP_DIR/shared/.env             $NEW/.env
ln -sfn $APP_DIR/shared/uploads          $NEW/wp-content/uploads

# Atomic symlink swap.
ln -sfn $NEW $APP_DIR/current.new
mv -Tf $APP_DIR/current.new $APP_DIR/current

cd $APP_DIR/current
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env build wordpress
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env up -d --remove-orphans

# Prune old releases (keep last 5).
ls -1dt $APP_DIR/releases/*/ | tail -n +6 | xargs -r rm -rf
```

**`/healthz` Nginx endpoint** — add to `docker/nginx/conf.d/site.conf`:

```nginx
location = /healthz { access_log off; return 200 "ok\n"; }
```

So the CI health-check doesn't depend on WordPress booting (the slowest piece).

### 5.3 DB push — `db-push.yml` + `deploy/db-import.sh`

**Trigger:** `workflow_dispatch` only. Never on push.

**Inputs:**
- `confirm` — must equal the literal string `OVERWRITE-PROD-DB`. Workflow fails immediately if it doesn't.
- `old_url` — default `http://localhost:8080` (verified against the current local DB).
- `new_url` — default `http://157.245.45.208`.

**Flow:**

```
You (local laptop)                 GitHub Actions                Server
─────────────────                  ──────────────                ──────
1. make db:dump  ─────────────┐
   (mysqldump → backup.sql.gz)│
   committed to snapshot branch│
                              │
2. gh workflow run db-push   ─┼──► 3. checkout snapshot branch
   --field confirm=OVERWRITE   │     4. scp deploy/snapshots/latest.sql.gz
              -PROD-DB         │        → server
                              │     5. ssh: trigger db-backup mysqldump
                              │        (insurance dump before import)
                              │     6. ssh:
                              │          docker compose exec -T db \
                              │            sh -c 'gunzip | mysql ...'
                              │     7. ssh:
                              │          docker compose exec wordpress \
                              │            wp search-replace \
                              │              "$OLD_URL" "$NEW_URL" \
                              │              --all-tables-with-prefix \
                              │              --precise --skip-columns=guid
                              │     8. ssh:
                              │          docker compose exec wordpress \
                              │            wp cache flush
```

**Where the dump lives:** A `Makefile` target on your laptop produces `deploy/snapshots/latest.sql.gz`. `.gitignore` keeps that path out of normal commits; you commit it to a **`snapshot/<date>`** branch when you want to push. The workflow checks out that branch.

(Artifact-upload via `gh workflow run --field` is the documented alternative if branch-commit feels heavy, but committing to a snapshot branch keeps everything in git and gives you a history of what was pushed when.)

**URL search-replace verified against current local DB:**
- `KAW_options.home` = `http://localhost:8080`
- `KAW_options.siteurl` = `http://localhost:8080`
- No residual production URLs in posts/postmeta — sweep across `groser`, `shobjiwala`, and generic `https?://` patterns returned only external references (Unsplash, YouTube, theme demo sites) which we leave alone.
- "groser" mentions in the DB are theme/widget names (parent theme is named `groser`, child theme `groser-child`), not URLs.

So the search-replace is a single command:

```bash
wp search-replace 'http://localhost:8080' 'http://157.245.45.208' \
    --all-tables-with-prefix --precise --skip-columns=guid
```

**Safety:**
- Step 5 forces a fresh `db-backup` dump immediately before import — a one-command undo if the new dump turns out to be wrong.
- The workflow runs `wp search-replace --dry-run` first and prints the result before doing it for real (visible in the GH Actions log).

### 5.4 Rollback — `rollback.yml` + `deploy/rollback.sh`

**Trigger:** `workflow_dispatch`. Input: `release` (free-text release timestamp; empty = "previous").

**Steps:**
1. SSH `ls -1dt /opt/shobjiwala/releases/` — pick the requested release, or the second-newest if input is empty.
2. SSH: atomic symlink flip (same pattern as `release.sh`).
3. SSH: `cd current && docker compose ... up -d`.
4. Health-check `http://$SERVER_HOST/healthz`.

Rollback restores **code only** (themes, plugins, vendor, configs). DB rollback is separate — restore from `db_backups` volume (`docker compose exec db-backup ls /backups`). The runbook in `docs/deployment.md` documents the manual DB-restore steps.

## 6. Files added to the repo

```
.github/workflows/
    server-bootstrap.yml      # one-time, manual
    deploy.yml                # manual, every release
    db-push.yml               # manual, dangerous (requires confirm string)
    rollback.yml              # manual

deploy/
    bootstrap.sh              # runs on server during bootstrap (idempotent)
    release.sh                # runs on server during deploy
    rollback.sh               # runs on server during rollback
    db-import.sh              # runs on server during db-push
    snapshots/
        .gitkeep
        # latest.sql.gz only on snapshot/<date> branches

docker/nginx/conf.d/
    site.conf                 # MODIFY — add /healthz endpoint

docs/deployment.md            # runbook

Makefile                      # NEW (or modify) — db:dump, db:push targets

.gitignore                    # MODIFY — ignore deploy/snapshots/*.sql.gz
```

The existing `docker-compose.yml` / `docker-compose.prod.yml` / `docker/nginx/nginx.conf` / mu-plugins remain untouched. The Nginx-in-front-of-Apache wiring you asked about is already present in the prod overlay.

## 7. Security recap

| Layer    | Control                                                                                       |
|----------|-----------------------------------------------------------------------------------------------|
| Network  | UFW: only 22/80/443; default-deny inbound                                                     |
| SSH      | Key-only, password disabled, root login restricted to your laptop key, fail2ban on sshd jail  |
| OS       | Unattended security upgrades, UTC clock                                                       |
| Docker   | Non-root `deploy` user in docker group, no sudo                                               |
| App      | Existing M4–M8 hardening (2FA, login throttling, xmlrpc closed, user-enum closed)             |
| DB       | Not exposed on host port (`ports: !reset []` in prod compose); root pw rotated; hourly backup |
| Redis    | Password-protected (`requirepass`); no host port                                              |
| Secrets  | Only on server in `/opt/shobjiwala/shared/.env` (mode 0600, owned by deploy); never in git    |
| CI       | One CI key, limited to `deploy@`, no sudo; bootstrap key deleted after first successful run   |
| Deploy   | Atomic symlink flip + auto-rollback on health-check failure                                   |

## 8. Server-migration runbook (when the droplet changes)

1. Provision new Ubuntu droplet at new IP.
2. Update `SERVER_HOST` GH Secret. Add new `BOOTSTRAP_SSH_KEY` if needed.
3. Run `server-bootstrap.yml`.
4. Update `WP_HOME` / `WP_SITEURL` in `/opt/shobjiwala/shared/.env` on the new server (or commit a new prod env template).
5. Run `db-push.yml` with `new_url` set to the new IP/domain.
6. Run `deploy.yml`.
7. Flip DNS (when a domain exists). Re-run `db-push.yml` with the domain as `new_url`.
8. Decommission old droplet.

## 9. Open questions / follow-ups

- **TLS / Let's Encrypt** — deferred until a domain points at the IP. Once that happens, add a `certbot-init.yml` workflow that runs certbot inside the Nginx container, then uncomment the HTTPS server block in `site.conf`.
- **Replica count** — `docker-compose.prod.yml` defaults to 2 WP replicas. With only 4 GB RAM, monitor memory after first deploy; reduce to 1 by overriding `deploy.replicas` if OOM happens.
- **Off-host backups** — `db-backup` writes to a docker volume on the same droplet. Adding nightly upload to S3 / DO Spaces is a low-effort follow-up.
- **Domain / DNS** — TBD by user when ready.
