#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "[flowdesk] Ensuring php + mysql services are available..."
docker compose up -d mysql php

echo "[flowdesk] Running backend tests..."
docker compose exec -T php php artisan test
