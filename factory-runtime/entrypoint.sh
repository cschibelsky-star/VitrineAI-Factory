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
: "${DB_DATABASE:=/app/database/database.sqlite}"

export APP_ENV APP_DEBUG APP_URL DB_CONNECTION DB_DATABASE

touch /app/database/database.sqlite

php artisan key:generate --force --no-interaction
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction || true
php artisan optimize:clear

exec php artisan serve --host=0.0.0.0 --port=8080
