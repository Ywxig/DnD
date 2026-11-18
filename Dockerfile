# Образ приложения: PHP 8.2 + Apache + pdo_pgsql, без фреймворков.
FROM php:8.2-apache

# libpq нужен для pdo_pgsql, unzip — для распаковки Composer-пакетов.
# mbstring и ctype уже включены в базовый образ php:8.2-apache.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_pgsql

# DocumentRoot указывает на public/ — только там лежит точка входа
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf

RUN a2enmod rewrite headers

# Composer и PHP-зависимости (Twig) устанавливаются в образ.
# В docker-compose папка vendor/ вынесена в отдельный volume.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist
