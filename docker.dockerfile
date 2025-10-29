FROM php:8.2-apache
WORKDIR /var/www/html
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
COPY composer.json composer.lock ./
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader \
    && php -r "unlink('composer-setup.php');"
COPY . .
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]