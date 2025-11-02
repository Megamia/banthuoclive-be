FROM php:8.2-fpm

# Nhận biến môi trường (Render hoặc CI sẽ set giá trị này)
ARG COMPOSER_AUTH_B64
ENV COMPOSER_AUTH_B64=$COMPOSER_AUTH_B64

# Log giá trị base64 để debug
RUN echo "=== DEBUG: GIÁ TRỊ COMPOSER_AUTH_B64 (base64) ===" && \
    echo "$COMPOSER_AUTH_B64" && \
    echo "=== DEBUG: GIẢI MÃ BASE64 THÀNH COMPOSER_AUTH ===" && \
    export COMPOSER_AUTH=$(echo "$COMPOSER_AUTH_B64" | base64 -d 2>/dev/null || echo "{}") && \
    echo "$COMPOSER_AUTH" > /tmp/auth.json && \
    echo "=== DEBUG: COMPOSER_AUTH (sau khi decode) ===" && \
    cat /tmp/auth.json

# Cài hệ thống
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

# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Thư mục làm việc
WORKDIR /var/www

# Copy code
COPY . .

# Debug và cài Composer packages
RUN set -eux; \
    echo "=== DEBUG: COMPOSER_AUTH TRONG BƯỚC CÀI PACKAGE ==="; \
    echo "$COMPOSER_AUTH"; \
    mkdir -p /root/.composer; \
    echo "$COMPOSER_AUTH" > /root/.composer/auth.json; \
    echo "=== DEBUG: NỘI DUNG /root/.composer/auth.json ==="; \
    cat /root/.composer/auth.json; \
    \
    php -r "json_decode(file_get_contents('/root/.composer/auth.json')); if (json_last_error() !== JSON_ERROR_NONE) { fwrite(STDERR, '❌ JSON không hợp lệ: '.json_last_error_msg().PHP_EOL); exit(1); }"; \
    \
    echo '=== DEBUG: BẮT ĐẦU COMPOSER INSTALL ==='; \
    composer install --ignore-platform-reqs --no-interaction --prefer-dist || { echo '❌ Composer install failed'; cat /root/.composer/auth.json; exit 1; }; \
    echo '=== DEBUG: COMPOSER INSTALL THÀNH CÔNG ==='; \
    rm /root/.composer/auth.json

# Chuẩn bị thư mục public & storage
RUN mkdir -p /var/www/public \
    && mkdir -p /var/www/storage/app/public

EXPOSE 8000

# Run Laravel dev server
CMD ["sh", "-c", "php artisan livotec:upload-images && php -S 0.0.0.0:8000 -t . vendor/october/rain/src/Foundation/resources/server.php"]
