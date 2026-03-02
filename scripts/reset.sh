#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "[flowdesk] Resetting development environment..."
docker compose down -v --remove-orphans
./scripts/setup.sh
