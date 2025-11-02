FROM php:8.2-fpm

ENV COMPOSER_AUTH=""

# Install system packages
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    curl \
    zip \
    nodejs \
    npm \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    cron \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy source code
COPY . .

# Cài package
RUN mkdir -p /root/.composer \
    && echo "$COMPOSER_AUTH" > /root/.composer/auth.json \
    && cat /root/.composer/auth.json \
    && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
    && rm /root/.composer/auth.json

RUN mkdir -p /var/www/public \
    && mkdir -p /var/www/storage/app/public

EXPOSE 8000

CMD ["sh", "-c", "php artisan livotec:upload-images && php -S 0.0.0.0:8000 -t . vendor/october/rain/src/Foundation/resources/server.php"]