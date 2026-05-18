# Shobjiwala — WordPress (composer-managed)

Local WordPress dev environment for the **Groser** grocery WooCommerce site. Themes are tracked in git; plugins are pulled by Composer. No seed database is bundled — first boot starts on an empty DB.

## Stack

- **WordPress** latest (apache + PHP 8.3) — core supplied by the official `wordpress` docker image. PHP 8.3 is the highest PHP that WordPress fully supports without exceptions.
- **MariaDB** 10.11 — `utf8mb4` / `utf8mb4_unicode_520_ci`.
- **phpMyAdmin** 5 — DB admin UI.
- **WP-CLI** — runnable on demand via the `wpcli` service.
- **Composer** — manages all plugins (and theme dependencies) from [wpackagist](https://wpackagist.org).

## Layout

```
.
├── composer.json                # Plugins managed via wpackagist
├── docker-compose.yml           # Dev: WP + MariaDB + phpMyAdmin
├── docker-compose.prod.yml      # Prod overlay: nginx LB, redis, db-backup
├── wp-config.php                # Mounted into the container (env-driven)
├── .env / .env.example          # Local credentials & ports
├── .env.prod.example            # Prod-only extras (REDIS_PASSWORD, …)
├── docker/
│   ├── wordpress/Dockerfile     # Custom WP image w/ WP-CLI baked in
│   ├── nginx/                   # Prod LB config + per-site server blocks
│   └── db-backup/               # Hourly mysqldump sidecar (24h rolling)
└── wp-content/
    ├── themes/                  # groser + groser-child (tracked in git)
    ├── plugins/                 # Installed by `composer install` (gitignored)
    ├── mu-plugins/              # MU plugins — shobjiwala-security.php tracked
    └── uploads/                 # Dev: bind-mounted. Prod: shared named volume.
```

## First-time setup

```bash
cp .env.example .env       # edit if you want to change ports/creds
composer install           # pulls plugins into wp-content/plugins
docker compose up -d       # boots db + WP on an empty database
```

Then visit:

- WordPress: <http://localhost:8080> — runs through the standard WP installer the first time
- phpMyAdmin: <http://localhost:8081>

To reset everything (DB volume + uploads), drop and recreate:

```bash
docker compose down -v
docker compose up -d
```

## Production

Production runs the same `docker-compose.yml` with a second file layered on top:

```bash
cp .env.prod.example .env       # fill REDIS_PASSWORD, WP_CACHE_KEY_SALT, etc.
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d \
    --scale wordpress=2
```

What the prod overlay adds:

| Service | Purpose |
| --- | --- |
| `nginx` | L7 load balancer (`least_conn`) + TLS termination. Listens on 80/443. ACME challenge endpoint on port 80. |
| `redis` | Shared object cache and PHP session store — mandatory once `wordpress` runs >1 replica, otherwise transients diverge per replica. |
| `db-backup` | Cron sidecar that runs `mysqldump | gzip` hourly into the `db_backups` named volume and prunes files older than 24 hours. |

And changes:

- `wordpress` replicates (`--scale wordpress=N`). No host port mapping — only nginx faces the internet.
- `uploads` switches from a host bind mount to a `wp_uploads` named volume so every replica writes to the same store.
- `wp-content/themes`, `plugins`, `mu-plugins` and `wp-config.php` mount read-only (deploys ship code via image rebuild).
- `phpmyadmin` is profile-gated off. Tunnel via SSH if you need DB admin: `ssh -L 33306:db:3306 user@host`.

### TLS bootstrap

`docker/nginx/conf.d/site.conf` ships HTTP-only by default. After the stack is up and DNS points to the host:

```bash
# Issue the first cert (run once)
docker run --rm -it \
  -v shobjiwala_nginx_certs:/etc/letsencrypt \
  -v shobjiwala_nginx_acme:/var/www/certbot \
  certbot/certbot certonly --webroot -w /var/www/certbot \
      -d your-domain.example -m you@example.com --agree-tos --no-eff-email
```

Then uncomment the `server { listen 443 ssl http2; … }` block in `docker/nginx/conf.d/site.conf` (it ships in place, commented), swap the `location /` in the HTTP server for `return 301 https://$host$request_uri;`, and reload:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec nginx nginx -s reload
```

### Backups — restore one

```bash
# List what's in the rolling window
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
  exec db-backup ls -lh /backups

# Restore (or sanity-check) a specific dump
docker run --rm -v shobjiwala_db_backups:/backups mariadb:10.11 \
  bash -c 'gunzip -t /backups/backup-2026-05-18T14-00.sql.gz && echo ok'
```

The 24-file retention is enforced inside the container; no host-side cron needed.

### Volumes — what's permanent

All persistent state lives in named Docker volumes. `docker compose down` leaves them intact; `docker compose down -v` destroys them. Backups go to `db_backups` regardless, so even a `down -v` recovery has a working path (restore the latest gzip into a freshly initialised `db_data`).

| Volume | Used in | Holds |
| --- | --- | --- |
| `shobjiwala_db_data` | dev, prod | MariaDB data files. |
| `shobjiwala_wp_uploads` | prod | Uploaded media, shared between WP replicas. |
| `shobjiwala_db_backups` | prod | Hourly mysqldumps, last 24 files. |
| `shobjiwala_redis_data` | prod | Redis RDB + AOF snapshot. |
| `shobjiwala_nginx_certs` | prod | Let's Encrypt cert + private key. |
| `shobjiwala_nginx_acme` | prod | ACME HTTP-01 challenge work dir. |

## Active plugins

Installed via composer:

- ajax-search-for-woocommerce
- akismet
- breadcrumb-navxt
- contact-form-7
- duplicate-attribute
- duplicate-page
- duplicator
- elementor
- google-analytics-for-wordpress (MonsterInsights)
- image-optimization
- jetpack
- mailchimp-for-wp
- one-click-demo-import
- optinmonster
- redis-cache (used by the prod stack — activate via `wp plugin activate redis-cache` after the prod overlay is up)
- svg-support
- woo-smart-quick-view
- woo-smart-wishlist
- woo-variation-swatches
- woocommerce
- wordpress-seo (Yoast)
- wpforms-lite

**Not installed** (premium / host-specific — sourced separately if needed):

- `bluehost-wordpress-plugin` — host-specific, not relevant locally.
- `revslider` — premium (Slider Revolution).
- `yith-paypal-payments-for-woocommerce-extended` — premium YITH.
- `yith-stripe-payments-for-woocommerce-extended` — premium YITH.
- `groser-tools` / `elementor-pro` / `elementor-theme-core` / `listo` / `theme-core-options` / `swa-import-export` — custom or premium add-ons bundled with the original theme; place them under `wp-content/plugins/` manually if you have the source.

## Quality gates

CI runs on every PR and on pushes to `main` (workflows live in `.github/workflows/`).

| Workflow | What it does | Blocking |
| --- | --- | --- |
| `coding-standards.yml` | PHPCS with WordPress-Extra + PHPCompatibilityWP on **our** code (`wp-config.php`, `wp-content/themes/groser-child/`, `wp-content/mu-plugins/`). | yes |
| `static-analysis.yml` | PHPStan level 1 with `szepeviktor/phpstan-wordpress` + WooCommerce stubs. Same scope. | yes |
| `security.yml` → `composer-audit` | `composer audit` against the Packagist Security Advisories DB. | yes |
| `security.yml` → `phpcs-security` | `WordPress.Security.*` + `WordPress.DB.*` sniffs on our code. | yes |
| `security.yml` → `phpcs-security-vendor` | Same security sniffs against the **parent Groser theme**. Findings are surfaced as PR annotations. | **informational** (`continue-on-error: true`) |
| `security.yml` → `trivy-fs` | Trivy filesystem CVE scan, HIGH/CRITICAL only, excluding composer-installed plugins. | yes |

Run the same checks locally:

```bash
composer install                     # pulls phpcs/phpstan/wpcs/security-advisories
composer lint                        # vendor/bin/phpcs        (WordPress-Extra, our scope)
composer lint:fix                    # vendor/bin/phpcbf
composer analyze                     # vendor/bin/phpstan analyse --memory-limit=1G
composer security                    # composer audit

vendor/bin/phpcs --standard=phpcs-security.xml.dist          # security sniffs (our scope)
vendor/bin/phpcs --standard=phpcs-security-vendor.xml.dist   # security sniffs (parent theme — informational)
```

`roave/security-advisories: dev-latest` is also a `require-dev` dependency, so `composer install` itself will fail locally if any package in the lock file has a published advisory.

## WP-CLI

WP-CLI is baked into the wordpress image (`docker/wordpress/Dockerfile`) so it runs against the same filesystem the web server uses:

```bash
docker compose exec -u www-data wordpress wp option get siteurl
docker compose exec -u www-data wordpress wp plugin list
docker compose exec -u www-data wordpress wp search-replace 'https://oldsite.example' 'http://localhost:8080'
docker compose exec -u www-data wordpress wp rewrite flush --hard
```

## Notes

- Table prefix: `KAW_` (matches the original install — kept so the existing `groser` theme and any future imports line up).
- `wp-content/uploads` is gitignored; populate via the WP media library or a separate import.
