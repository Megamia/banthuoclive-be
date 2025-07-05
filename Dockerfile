# Sử dụng PHP 8.1 có FPM (thường dùng với OctoberCMS)
FROM php:8.1-fpm

# Cập nhật và cài các gói hệ thống cần thiết
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_mysql zip mbstring gd

# Cài Composer (phiên bản 2 mới nhất)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Tạo thư mục chứa source code
WORKDIR /var/www

# Copy toàn bộ project vào container
COPY . .

# ✅ Thêm file auth.json để composer có thể tải gói từ OctoberCMS (tránh lỗi 403)
COPY auth.json /root/.composer/auth.json

# ✅ Cài đặt các package PHP từ composer
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist \
    && rm /root/.composer/auth.json

# Gán quyền cho các file nếu cần
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Expose cổng mặc định của PHP-FPM
EXPOSE 9000

# Khởi động PHP-FPM
CMD ["php-fpm"]
