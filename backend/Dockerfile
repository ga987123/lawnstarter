# ─────────────────────────────────────────────────────────────────────────────
# Stage: Composer dependencies (development – includes dev deps)
# ─────────────────────────────────────────────────────────────────────────────
FROM composer:2 AS vendor-dev
WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-req=ext-sockets \
    && composer dump-autoload --optimize --no-scripts

# ─────────────────────────────────────────────────────────────────────────────
# Stage: Composer dependencies (production – no dev deps)
# ─────────────────────────────────────────────────────────────────────────────
FROM composer:2 AS vendor-prod
WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-req=ext-sockets \
    && composer dump-autoload --optimize --no-dev --no-scripts

# ─────────────────────────────────────────────────────────────────────────────
# Stage: Base PHP runtime
# ─────────────────────────────────────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
    curl \
    git \
    libzip-dev \
    linux-headers \
    unzip \
    && docker-php-ext-install zip pcntl sockets

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis pcov \
    && docker-php-ext-enable redis pcov \
    && apk del .build-deps

WORKDIR /var/www/html

# ─────────────────────────────────────────────────────────────────────────────
# Stage: Development (Composer kept for one-off installs)
# ─────────────────────────────────────────────────────────────────────────────
FROM base AS development

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
COPY . .

COPY --from=vendor-dev /app/vendor ./vendor

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

# ─────────────────────────────────────────────────────────────────────────────
# Stage: Production (no Composer in final image)
# ─────────────────────────────────────────────────────────────────────────────
FROM base AS production

COPY composer.json composer.lock* ./
COPY . .

COPY --from=vendor-prod /app/vendor ./vendor

RUN php artisan config:cache \
    && php artisan route:cache

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
