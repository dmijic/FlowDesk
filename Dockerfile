FROM composer:2 AS vendor

WORKDIR /app/backend

COPY backend/composer.json backend/composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM php:8.3-fpm-alpine AS app

RUN apk add --no-cache \
    bash \
    curl \
    icu-dev \
    libzip-dev \
    linux-headers \
    mariadb-client \
    oniguruma-dev \
    unzip \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
    && rm -rf /tmp/* /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

COPY backend/ /var/www/backend/
COPY --from=vendor /app/backend/vendor /var/www/backend/vendor
COPY docker/entrypoint.sh /usr/local/bin/flowdesk-entrypoint
COPY docker/php/conf.d/production.ini /usr/local/etc/php/conf.d/zz-flowdesk.ini

RUN chmod +x /usr/local/bin/flowdesk-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/backend

ENTRYPOINT ["flowdesk-entrypoint"]
CMD ["php-fpm", "-F"]
