FROM php:8.2-fpm

RUN apt-get update && apt-get install -y zip unzip git libpng-dev libonig-dev libxml2-dev \
  && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader \
 && php artisan key:generate \
 && chown -R www-data:www-data storage bootstrap/cache
CMD ["php-fpm"]

