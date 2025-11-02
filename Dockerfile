FROM php:8.2-fpm

# Biến môi trường (Render sẽ override giá trị này)
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

# Debug và cài Composer packages
RUN set -eux; \
    echo "=== DEBUG: COMPOSER_AUTH VALUE START ==="; \
    echo "$COMPOSER_AUTH"; \
    echo "=== DEBUG: COMPOSER_AUTH VALUE END ==="; \
    \
    mkdir -p /root/.composer; \
    echo "$COMPOSER_AUTH" > /root/.composer/auth.json; \
    echo "=== DEBUG: SAVED /root/.composer/auth.json ==="; \
    cat /root/.composer/auth.json; \
    \
    # Kiểm tra JSON hợp lệ trước khi cài
    php -r "json_decode(file_get_contents('/root/.composer/auth.json')); if (json_last_error() !== JSON_ERROR_NONE) { fwrite(STDERR, '❌ COMPOSER_AUTH JSON không hợp lệ: '.json_last_error_msg().PHP_EOL); exit(1); }"; \
    \
    echo "=== DEBUG: BẮT ĐẦU CÀI COMPOSER PACKAGES ==="; \
    composer install --ignore-platform-reqs --no-interaction --prefer-dist || { echo '❌ Composer install failed'; cat /root/.composer/auth.json; exit 1; }; \
    echo "=== DEBUG: CÀI COMPOSER THÀNH CÔNG ==="; \
    \
    rm /root/.composer/auth.json

# Chuẩn bị thư mục public & storage
RUN mkdir -p /var/www/public \
    && mkdir -p /var/www/storage/app/public

EXPOSE 8000

# Run Laravel dev server
CMD ["sh", "-c", "php artisan livotec:upload-images && php -S 0.0.0.0:8000 -t . vendor/october/rain/src/Foundation/resources/server.php"]