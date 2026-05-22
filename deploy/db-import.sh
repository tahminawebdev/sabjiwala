#!/usr/bin/env bash
# On-server DB import + URL search-replace.
# Args:
#   $1 = absolute path to .sql.gz dump file on the server
#   $2 = OLD_URL (e.g. http://localhost:8080)
#   $3 = NEW_URL (e.g. http://157.245.45.208)
#   $4 = "--apply" to actually run search-replace; otherwise dry-run only.
# Runs as `deploy` user.
set -euo pipefail

DUMP="${1:?dump file path required}"
OLD_URL="${2:?OLD_URL required}"
NEW_URL="${3:?NEW_URL required}"
MODE="${4:---dry-run}"

APP_DIR=/opt/shobjiwala
log() { printf '[db-import] %s\n' "$*"; }

[ -f "$DUMP" ] || { log "FATAL: dump $DUMP not found"; exit 1; }
cd "$APP_DIR/current"

# --- 1. Insurance dump --------------------------------------------------------
log "Triggering an insurance backup via db-backup container"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env exec -T db-backup /usr/local/bin/backup.sh || \
    log "WARN: insurance backup failed — proceeding anyway"

# --- 2. Import ----------------------------------------------------------------
log "Importing $DUMP into the db container"
# shellcheck disable=SC2016  # $-vars are intentionally shell-expanded inside the container
gunzip -c "$DUMP" | docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env exec -T db sh -c \
    'mysql -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"'

# --- 3. URL search-replace ----------------------------------------------------
log "Search-replace: $OLD_URL → $NEW_URL (mode: $MODE)"
SR_ARGS=(
    "$OLD_URL" "$NEW_URL"
    --all-tables-with-prefix
    --precise
    --skip-columns=guid
    --allow-root
)
if [ "$MODE" = "--apply" ]; then
    docker compose -f docker-compose.yml -f docker-compose.prod.yml \
        --env-file .env exec -T wordpress wp search-replace "${SR_ARGS[@]}"
    log "Flushing object cache"
    docker compose -f docker-compose.yml -f docker-compose.prod.yml \
        --env-file .env exec -T wordpress wp cache flush --allow-root
else
    log "Dry-run only — pass --apply to commit"
    docker compose -f docker-compose.yml -f docker-compose.prod.yml \
        --env-file .env exec -T wordpress wp search-replace "${SR_ARGS[@]}" --dry-run
fi

log "Done."
