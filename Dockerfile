FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx git unzip curl zip nodejs npm \
    libpng-dev libonig-dev libxml2-dev libzip-dev cron \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath \
    && curl -sS https://getcomposer.org/installer \
        | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

ENV COMPOSER_HOME=/tmp/composer

WORKDIR /var/www
COPY . .

RUN mkdir -p /tmp/composer \
    && chown -R www-data:www-data /var/www /tmp/composer

EXPOSE 8000

USER www-data

CMD ["sh", "-c", "\
echo \"$COMPOSER_AUTH\" > $COMPOSER_HOME/auth.json && \
composer install --ignore-platform-reqs --no-interaction --prefer-dist && \
php artisan livotec:upload-images && \
php -S 0.0.0.0:8000 -t . vendor/october/rain/src/Foundation/resources/server.php \
"]