FROM php:8.2-apache
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
EXPOSE 10000
CMD ["apache2-foreground"]
