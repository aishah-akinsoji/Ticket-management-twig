FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install intl pdo_mysql zip

COPY . /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-dev --optimize-autoloader

RUN a2enmod rewrite
RUN sed -i 's|/var/www/html/public|/var/www/html|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
