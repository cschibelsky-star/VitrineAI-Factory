FROM composer:2 AS build

WORKDIR /workspace
COPY . /workspace

RUN composer create-project laravel/laravel:^12.0 /app --no-interaction --prefer-dist \
    && cd /app \
    && composer require filament/filament:^4.0 --with-all-dependencies --no-interaction \
    && php artisan filament:install --panels --no-interaction \
    && cp -a /workspace/factory-core/app/. /app/app/ \
    && cp -a /workspace/factory-core/database/. /app/database/ \
    && cp -a /workspace/factory-core/resources/. /app/resources/

FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev unzip \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=build /usr/bin/composer /usr/bin/composer
COPY --from=build /app /app
COPY factory-runtime/entrypoint.sh /usr/local/bin/factory-entrypoint

WORKDIR /app
RUN chmod +x /usr/local/bin/factory-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["factory-entrypoint"]
