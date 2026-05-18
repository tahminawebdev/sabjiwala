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
├── composer.json          # Plugins managed via wpackagist
├── docker-compose.yml     # WP + MariaDB + phpMyAdmin + WP-CLI
├── wp-config.php          # Mounted into the container (env-driven)
├── .env / .env.example    # Local credentials & ports
└── wp-content/
    ├── themes/            # groser + groser-child (tracked in git)
    ├── plugins/           # Installed by `composer install` (gitignored)
    ├── mu-plugins/        # MU plugins (gitignored)
    └── uploads/           # User uploads (gitignored)
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
