FROM php:8.4-apache

WORKDIR /var/www/html

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libpng-dev libzip-dev libxml2-dev libpq-dev libonig-dev \
    && docker-php-ext-install intl pdo_mysql zip gd opcache \
    && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
