FROM php:8.3-cli AS build

RUN apt-get update \
    && apt-get install -y --no-install-recommends git libicu-dev libonig-dev libsqlite3-dev libzip-dev unzip \
    && docker-php-ext-install intl mbstring pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

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
    && apt-get install -y --no-install-recommends curl libicu-dev libonig-dev libsqlite3-dev libzip-dev unzip \
    && docker-php-ext-install intl mbstring pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=build /app /app
COPY factory-runtime/entrypoint.sh /usr/local/bin/factory-entrypoint

WORKDIR /app
RUN chmod +x /usr/local/bin/factory-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/admin >/dev/null || exit 1

ENTRYPOINT ["factory-entrypoint"]
