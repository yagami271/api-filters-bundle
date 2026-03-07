FROM php:8.3-cli-alpine

RUN apk --no-cache add git unzip icu-dev libzip-dev postgresql-dev mysql-dev linux-headers \
    && docker-php-ext-install intl zip pdo_mysql pdo_pgsql \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
