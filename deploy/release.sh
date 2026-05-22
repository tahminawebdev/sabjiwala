#!/usr/bin/env bash
# Atomic on-server release: link shared bits, flip symlink, docker compose up.
# Arg: $1 = RELEASE_TS (timestamp dir under /opt/shobjiwala/releases/)
# Runs as `deploy` user.
set -euo pipefail

RELEASE_TS="${1:?RELEASE_TS arg required}"
APP_DIR=/opt/shobjiwala
NEW="$APP_DIR/releases/$RELEASE_TS"
SHARED="$APP_DIR/shared"

log() { printf '[release] %s\n' "$*"; }

[ -d "$NEW" ] || { log "FATAL: $NEW does not exist"; exit 1; }
[ -f "$SHARED/.env" ] || { log "FATAL: $SHARED/.env missing — populate it before deploy"; exit 1; }

# --- 1. Link shared state into the new release --------------------------------
log "Linking shared .env and uploads into $NEW"
ln -sfn "$SHARED/.env" "$NEW/.env"
mkdir -p "$NEW/wp-content"
ln -sfn "$SHARED/uploads" "$NEW/wp-content/uploads"

# --- 2. Atomic symlink swap ---------------------------------------------------
log "Swapping current → $RELEASE_TS"
ln -sfn "$NEW" "$APP_DIR/current.new"
mv -Tf "$APP_DIR/current.new" "$APP_DIR/current"

# --- 3. docker compose up -----------------------------------------------------
cd "$APP_DIR/current"
log "docker compose build wordpress"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env build wordpress
log "docker compose up -d"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env up -d --remove-orphans

# Nginx bind-mounts its conf from the release dir; the running container
# resolves the path at start time, so a symlink flip alone is invisible
# to it. Force-recreate nginx so it re-binds to the new release's conf.
log "Force-recreating nginx so it re-binds to the new release's conf"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env up -d --force-recreate --no-deps nginx

# --- 4. Fix uploads ownership (the named volume is created as root:root on
#        first up; www-data must own it so WP can write Elementor CSS,
#        WooCommerce CSV imports, media uploads, etc.). Idempotent.
log "Ensuring www-data owns wp-content/uploads in the running WP container"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env exec -T -u root wordpress \
    sh -c 'chown -R www-data:www-data /var/www/html/wp-content/uploads && chmod -R u+rwX,g+rwX /var/www/html/wp-content/uploads'

# --- 5. Prune old releases ----------------------------------------------------
log "Pruning old releases (keeping last 5)"
# shellcheck disable=SC2012
# ls -dt gives reliable mtime order; find+stat is not portable across distros
ls -1dt "$APP_DIR/releases/"*/ 2>/dev/null | tail -n +6 | xargs -r rm -rf

log "Release $RELEASE_TS active."
