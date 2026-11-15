# Образ приложения: PHP 8.2 + Apache + pdo_pgsql, без фреймворков.
FROM php:8.2-apache

# Драйвер PostgreSQL для PDO
RUN docker-php-ext-install pdo_pgsql

# DocumentRoot указывает на public/ — только там лежит точка входа
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf

RUN a2enmod rewrite headers
