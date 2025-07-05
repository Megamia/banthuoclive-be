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
    zip \
    cron

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy source code
COPY . .

# Cài gói PHP qua Composer
RUN mkdir -p /root/.composer \
 && echo "$COMPOSER_AUTH" > /root/.composer/auth.json \
 && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
 && rm /root/.composer/auth.json

# Không cần EXPOSE nếu Railway tự inject
# EXPOSE ${PORT}

# CMD dùng Shell form để PORT hoạt động đúng
CMD sh -c 'php artisan serve --host=0.0.0.0 --port=${PORT}'
