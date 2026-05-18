#!/usr/bin/env bash
#
# Hourly mysqldump with a rolling retention window.
# Env (set by docker-compose.prod.yml):
#   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, RETENTION_HOURS
#
# Writes /backups/backup-YYYY-MM-DDTHH-MM.sql.gz on success, prunes any
# .sql.gz older than RETENTION_HOURS, and logs a one-line status to stdout
# (collected by `docker compose logs db-backup`).

set -euo pipefail

: "${DB_HOST:?DB_HOST required}"
: "${DB_PORT:=3306}"
: "${DB_NAME:?DB_NAME required}"
: "${DB_USER:?DB_USER required}"
: "${DB_PASSWORD:?DB_PASSWORD required}"
: "${RETENTION_HOURS:=24}"

BACKUP_DIR="/backups"
TS="$(date -u +'%Y-%m-%dT%H-%M')"
OUT="${BACKUP_DIR}/backup-${TS}.sql.gz"
TMP="${OUT}.partial"

mkdir -p "$BACKUP_DIR"

# --single-transaction = consistent snapshot without locking InnoDB tables.
# --quick              = stream rows instead of buffering the whole table.
# --routines           = include stored procs / functions.
# --triggers           = include triggers.
# --no-tablespaces     = avoid the PROCESS privilege requirement on MariaDB 10.5+.
if mysqldump \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --user="$DB_USER" \
        --password="$DB_PASSWORD" \
        --single-transaction \
        --quick \
        --routines \
        --triggers \
        --no-tablespaces \
        --default-character-set=utf8mb4 \
        "$DB_NAME" 2>/var/log/backup.err \
    | gzip -c > "$TMP"; then
    mv "$TMP" "$OUT"
    size="$(stat -c %s "$OUT")"
    echo "[$(date -u +'%Y-%m-%dT%H:%M:%SZ')] ok  size=${size}B file=$(basename "$OUT")"
else
    rm -f "$TMP"
    echo "[$(date -u +'%Y-%m-%dT%H:%M:%SZ')] FAIL dump failed; see /var/log/backup.err" >&2
    exit 1
fi

# Retention. RETENTION_HOURS converts to minutes for `find -mmin`.
prune_after_minutes=$(( RETENTION_HOURS * 60 ))
pruned=$(find "$BACKUP_DIR" -maxdepth 1 -name 'backup-*.sql.gz' -mmin "+${prune_after_minutes}" -print -delete | wc -l | tr -d ' ')
echo "[$(date -u +'%Y-%m-%dT%H:%M:%SZ')] retention pruned=${pruned} older_than=${RETENTION_HOURS}h"
