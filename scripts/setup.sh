#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

cp -n .env.example .env || true
cp -n backend/.env.example backend/.env || true
cp -n frontend/.env.example frontend/.env || true

echo "[flowdesk] Starting containers..."
docker compose up -d --build

echo "[flowdesk] Waiting for MySQL healthcheck..."
for i in {1..60}; do
  STATUS=$(docker inspect --format='{{json .State.Health.Status}}' flowdesk-mysql 2>/dev/null || true)
  if [[ "$STATUS" == '"healthy"' ]]; then
    break
  fi
  sleep 2
done

if [[ "${STATUS:-}" != '"healthy"' ]]; then
  echo "[flowdesk] MySQL did not become healthy in time."
  exit 1
fi

echo "[flowdesk] Installing backend dependencies..."
docker compose exec -T php composer install --no-interaction

echo "[flowdesk] Generating app key and running migrations + seed..."
docker compose exec -T php php artisan key:generate --force
docker compose exec -T php php artisan migrate:fresh --seed --force
docker compose exec -T php php artisan optimize:clear

echo "[flowdesk] Installing frontend dependencies..."
docker compose exec -T node npm install

echo "[flowdesk] Setup complete"
echo "Frontend (nginx): http://localhost"
echo "Frontend (vite):  http://localhost:5173"
echo "API:              http://localhost/api"
echo "Mailhog:          http://localhost:8025"
