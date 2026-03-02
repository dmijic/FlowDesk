#!/usr/bin/env sh
set -eu

cd /var/www/backend

role="${CONTAINER_ROLE:-app}"
run_migrations="${RUN_MIGRATIONS:-false}"
run_seed="${RUN_SEED:-false}"

ensure_app_key() {
  if [ -z "${APP_KEY:-}" ]; then
    echo "[flowdesk] APP_KEY is missing. Set it in backend/.env before starting production."
    exit 1
  fi
}

wait_for_db() {
  if [ "${SKIP_DB_WAIT:-false}" = "true" ]; then
    return
  fi

  php <<'PHP'
<?php
$host = getenv('DB_HOST') ?: 'mysql';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'flowdesk';
$username = getenv('DB_USERNAME') ?: 'flowdesk';
$password = getenv('DB_PASSWORD') ?: '';
$attempts = (int) (getenv('DB_WAIT_ATTEMPTS') ?: 60);
$sleep = (int) (getenv('DB_WAIT_SLEEP') ?: 2);

for ($i = 0; $i < $attempts; $i++) {
    try {
        new PDO("mysql:host={$host};port={$port};dbname={$database}", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        exit(0);
    } catch (Throwable $exception) {
        fwrite(STDERR, "[flowdesk] Waiting for database...\n");
        sleep($sleep);
    }
}

fwrite(STDERR, "[flowdesk] Database connection timed out.\n");
exit(1);
PHP
}

warm_app_cache() {
  php artisan optimize:clear
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
}

prepare_storage() {
  php artisan storage:link || true
}

ensure_app_key
wait_for_db

case "$role" in
  app)
    prepare_storage

    if [ "$run_migrations" = "true" ]; then
      php artisan migrate --force
    fi

    if [ "$run_seed" = "true" ]; then
      php artisan db:seed --force
    fi

    warm_app_cache
    exec "$@"
    ;;
  queue)
    php artisan config:cache
    exec php artisan queue:work --sleep="${QUEUE_SLEEP:-1}" --tries="${QUEUE_TRIES:-3}" --timeout="${QUEUE_TIMEOUT:-90}"
    ;;
  scheduler)
    php artisan config:cache
    while true; do
      php artisan schedule:run --no-interaction
      sleep "${SCHEDULER_INTERVAL:-60}"
    done
    ;;
  *)
    exec "$@"
    ;;
esac
