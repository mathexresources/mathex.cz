#!/usr/bin/env bash
set -euo pipefail

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
APP_SERVICE="${APP_SERVICE:-app}"

echo "==> Pulling latest code…"
git pull --ff-only

echo "==> Building production image…"
docker-compose -f "$COMPOSE_FILE" build --no-cache "$APP_SERVICE"

echo "==> Running database migrations…"
docker-compose -f "$COMPOSE_FILE" run --rm "$APP_SERVICE" php bin/console app:migrate

echo "==> Clearing application cache…"
docker-compose -f "$COMPOSE_FILE" run --rm "$APP_SERVICE" php bin/console app:clear-cache

echo "==> Regenerating sitemap…"
docker-compose -f "$COMPOSE_FILE" run --rm "$APP_SERVICE" php bin/console app:generate-sitemap

echo "==> Restarting services…"
docker-compose -f "$COMPOSE_FILE" up -d --remove-orphans

echo "==> Waiting for health check…"
sleep 5
docker-compose -f "$COMPOSE_FILE" ps

echo ""
echo "✔  Deploy complete."
