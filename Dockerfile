FROM php:8.2-fpm

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
    && docker-php-ext-install pdo mbstring zip exif pcntl bcmath

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy source code (KHÔNG COPY .env vào trong image)
COPY . .

# 👇 Setup COMPOSER_AUTH from build argument (KHÔNG hardcode key trong Dockerfile)
ARG COMPOSER_AUTH
ENV COMPOSER_AUTH=${COMPOSER_AUTH}

# Install PHP dependencies
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

# Install JS dependencies
RUN npm install && npm run build

# Expose port for Laravel dev server
EXPOSE 8000

# Start Laravel dev server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
