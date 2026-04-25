#!/usr/bin/env bash
set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-/var/backups/mathex}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
KEEP_DAYS="${KEEP_DAYS:-30}"
DATE="$(date +%Y%m%d_%H%M%S)"

mkdir -p "$BACKUP_DIR"

echo "==> [${DATE}] Starting backup…"

# ─── MySQL dump ───────────────────────────────────────────────────────────────
DB_FILE="${BACKUP_DIR}/mysql_${DATE}.sql.gz"
echo "    Dumping MySQL → ${DB_FILE}"
docker-compose -f "$COMPOSE_FILE" exec -T mysql \
    sh -c 'exec mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
    | gzip > "$DB_FILE"

# ─── Uploads ──────────────────────────────────────────────────────────────────
UPLOADS_FILE="${BACKUP_DIR}/uploads_${DATE}.tar.gz"
echo "    Archiving uploads → ${UPLOADS_FILE}"
docker-compose -f "$COMPOSE_FILE" exec -T app \
    tar -czf - -C /var/www/html/www uploads 2>/dev/null \
    > "$UPLOADS_FILE" || echo "    (no uploads directory – skipping)"

# ─── Prune old backups ────────────────────────────────────────────────────────
echo "    Pruning backups older than ${KEEP_DAYS} days…"
find "$BACKUP_DIR" -type f \( -name "*.sql.gz" -o -name "*.tar.gz" \) \
    -mtime +"$KEEP_DAYS" -delete

echo "==> Backup complete."
ls -lh "$BACKUP_DIR" | tail -10
