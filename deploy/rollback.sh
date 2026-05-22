#!/usr/bin/env bash
# Re-symlink `current` to a prior release and bounce docker compose.
# Arg: $1 = target release dir name (e.g. 2026-05-22T14-30-00Z).
#         If empty, picks the second-newest release.
# Runs as `deploy` user.
set -euo pipefail

TARGET="${1:-}"
APP_DIR=/opt/shobjiwala
log() { printf '[rollback] %s\n' "$*"; }

if [ -z "$TARGET" ]; then
    TARGET="$(ls -1dt "$APP_DIR/releases/"*/ 2>/dev/null | sed -n '2p' | xargs -r basename)"
    [ -n "$TARGET" ] || { log "FATAL: no prior release found"; exit 1; }
    log "No release specified — using previous: $TARGET"
fi

PREV="$APP_DIR/releases/$TARGET"
[ -d "$PREV" ] || { log "FATAL: $PREV does not exist"; exit 1; }

# Re-link shared bits (defensive — release.sh does this on initial deploy but
# the prev release may have lost its symlinks during a partial deploy).
ln -sfn "$APP_DIR/shared/.env" "$PREV/.env"
mkdir -p "$PREV/wp-content"
ln -sfn "$APP_DIR/shared/uploads" "$PREV/wp-content/uploads"

log "Swapping current → $TARGET"
ln -sfn "$PREV" "$APP_DIR/current.new"
mv -Tf "$APP_DIR/current.new" "$APP_DIR/current"

cd "$APP_DIR/current"
log "docker compose up -d (post-rollback)"
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
    --env-file .env up -d --remove-orphans

log "Rollback to $TARGET complete."
