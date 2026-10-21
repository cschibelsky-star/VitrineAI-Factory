#!/bin/sh
set -eu

cd /app

if [ ! -f .env ]; then
  cp .env.example .env
fi

: "${APP_ENV:=local}"
: "${APP_DEBUG:=true}"
: "${APP_URL:=http://localhost:8080}"
: "${DB_CONNECTION:=sqlite}"
: "${DB_DATABASE:=/app/storage/app/factory.sqlite}"

if [ -z "${APP_KEY:-}" ]; then
  key_file="/app/storage/app/factory-app-key"

  if [ -s "$key_file" ]; then
    APP_KEY="$(cat "$key_file")"
  else
    umask 077
    APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
    printf '%s' "$APP_KEY" > "$key_file"
  fi
fi

export APP_ENV APP_DEBUG APP_URL APP_KEY DB_CONNECTION DB_DATABASE

mkdir -p "$(dirname "$DB_DATABASE")"
touch "$DB_DATABASE"

# HML/production must not silently mutate the database during a service start.
# CI uses an isolated disposable SQLite volume with an explicit bootstrap gate.
if [ "${FACTORY_BOOTSTRAP_MODE:-}" = "ci" ] && [ "${CI:-}" = "true" ]; then
  php artisan migrate --force --no-interaction
  php artisan db:seed --force --no-interaction
fi
php artisan optimize:clear

exec php artisan serve --host=0.0.0.0 --port=8080
