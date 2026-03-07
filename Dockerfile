FROM php:8.3-cli-alpine

RUN apk --no-cache add git unzip icu-dev libzip-dev \
    && docker-php-ext-install intl zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
