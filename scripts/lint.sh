#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "[flowdesk] Running backend style checks..."
docker compose run --rm --no-deps php sh -lc './vendor/bin/pint --test'

echo "[flowdesk] Running frontend lint..."
docker compose run --rm --no-deps node sh -lc 'npm run lint'
