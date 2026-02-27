FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    libzip-dev \
    mysql-client \
    oniguruma-dev \
    unzip \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

RUN docker-php-ext-install \
    intl \
    pdo_mysql \
    bcmath \
    pcntl \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

CMD ["php-fpm"]
