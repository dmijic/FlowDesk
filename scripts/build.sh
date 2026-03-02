#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "[flowdesk] Building frontend assets..."
docker compose run --rm --no-deps node sh -lc 'npm run build'

echo "[flowdesk] Building production Docker images..."
docker compose -f docker-compose.prod.yml build
