FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx git unzip curl zip nodejs npm libpng-dev libonig-dev libxml2-dev libzip-dev cron \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
COPY . .

EXPOSE 8000

CMD ["sh", "-c", "\
mkdir -p /root/.composer && \
echo \"$COMPOSER_AUTH\" > /root/.composer/auth.json && \
composer install --ignore-platform-reqs --no-interaction --prefer-dist && \
php artisan livotec:upload-images && \
php -S 0.0.0.0:8000 -t . vendor/october/rain/src/Foundation/resources/server.php \
"]