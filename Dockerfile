FROM php:8.2

# Cài extension và gói cần thiết
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy mã nguồn vào container
COPY . /var/www

WORKDIR /var/www

# Cài package Laravel/OctoberCMS
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

# Expose port Railway yêu cầu
EXPOSE 8080

# Lệnh khởi chạy Laravel (hoặc OctoberCMS)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
