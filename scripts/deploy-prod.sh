#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
  echo "[flowdesk] Missing root .env. Copy .env.example and fill production values first."
  exit 1
fi

if [[ ! -f backend/.env ]]; then
  echo "[flowdesk] Missing backend/.env. Copy backend/.env.example and fill production values first."
  exit 1
fi

echo "[flowdesk] Building production images..."
docker compose -f docker-compose.prod.yml build --pull

echo "[flowdesk] Starting production stack..."
docker compose -f docker-compose.prod.yml up -d

echo "[flowdesk] Production stack is up. Check logs if needed:"
echo "docker compose -f docker-compose.prod.yml logs -f --tail=200"
