FROM php:8.2-fpm

# Inject ENV từ Railway
ARG OCTOBER_AUTH_JSON
ENV COMPOSER_AUTH=$OCTOBER_AUTH_JSON

# Install OS packages + PHP extensions
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
    cron \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy source code
COPY . .

# Tạo auth.json rồi chạy composer install
RUN mkdir -p /root/.composer \
 && printf '%s' "$COMPOSER_AUTH" > /root/.composer/auth.json \
 && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
 && rm /root/.composer/auth.json

# Quyền thư mục runtime/cache (tránh lỗi ghi file)
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data /var/www \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]

