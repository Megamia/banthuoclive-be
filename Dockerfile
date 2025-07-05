FROM php:8.2

# Cài extension và gói cần thiết
RUN apt-get update && apt-get install -y \
    git unzip curl zip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Tạo thư mục project và set quyền
WORKDIR /var/www
COPY . /var/www

# Set quyền (tránh lỗi ghi file runtime/cache/logs)
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Cài package Laravel hoặc OctoberCMS
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

# EXPOSE port Railway yêu cầu
EXPOSE 8080

# Chạy OctoberCMS (Laravel-based) server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
