#!/bin/bash
# Nightly backup: a gzipped SQL dump plus the uploaded media/attachments volume.
set -euo pipefail

RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"
RUN_AT="${BACKUP_AT:-03:30}"

backup() {
    local stamp
    stamp="$(date +%Y%m%d-%H%M%S)"
    mysqldump -h mysql -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --no-tablespaces "$MYSQL_DATABASE" | gzip > "/backups/db-$stamp.sql.gz"
    tar -czf "/backups/storage-$stamp.tar.gz" -C /data storage
    find /backups -type f \( -name 'db-*.sql.gz' -o -name 'storage-*.tar.gz' \) -mtime +"$RETENTION_DAYS" -delete
    echo "[$(date -Is)] backup $stamp done"
}

[ "${1:-}" = "now" ] && { backup; exit 0; }

while true; do
    next="$(date -d "$RUN_AT" +%s)"
    now="$(date +%s)"
    [ "$next" -le "$now" ] && next="$(date -d "tomorrow $RUN_AT" +%s)"
    sleep $((next - now))
    backup || echo "[$(date -Is)] backup FAILED" >&2
done
