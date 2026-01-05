FROM php:8.2-fpm

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git unzip curl zip cron \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
 && docker-php-ext-install \
    pdo pdo_mysql mbstring zip exif pcntl bcmath \
 && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
 && apt-get install -y nodejs

RUN curl -sS https://getcomposer.org/installer \
 | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
COPY . .

RUN chown -R www-data:www-data /var/www

USER www-data

EXPOSE 8000

CMD ["sh", "-c", "\
composer install --ignore-platform-reqs --no-interaction --prefer-source && \
php artisan livotec:upload-images && \
php -S 0.0.0.0:8000 -t . vendor/october/rain/src/Foundation/resources/server.php \
"]
