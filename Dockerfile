# Образ приложения: PHP 8.2 + Apache + pdo_pgsql, без фреймворков.
FROM php:8.2-apache

# Заголовки libpq нужны для сборки pdo_pgsql, затем чистим кэш apt
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_pgsql

# DocumentRoot указывает на public/ — только там лежит точка входа
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf

RUN a2enmod rewrite headers
