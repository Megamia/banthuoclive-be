FROM php:8.2-fpm

# Inject ENV từ Railway
ARG OCTOBER_AUTH_JSON
ENV COMPOSER_AUTH=$OCTOBER_AUTH_JSON

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

# Tạo auth.json rồi cài Composer
RUN mkdir -p /root/.composer \
    && echo "$COMPOSER_AUTH" > /root/.composer/auth.json \
    && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
    && rm /root/.composer/auth.json

RUN mkdir -p /var/www/public \
    && mkdir -p /var/www/storage/app/public


EXPOSE 8000

# Run Laravel dev server
CMD ["php", "-S", "0.0.0.0:8000", "-t", ".", "vendor/october/rain/src/Foundation/resources/server.php"]