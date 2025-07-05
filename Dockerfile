FROM php:8.1-fpm

# Cài các thư viện cần thiết
RUN apt-get update && apt-get install -y \
    git unzip curl zip libzip-dev libpng-dev libonig-dev libxml2-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_mysql zip mbstring gd

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# ✅ Gán COMPOSER_AUTH từ biến môi trường (được Railway inject khi build)
# Composer sẽ tự động đọc từ biến môi trường COMPOSER_AUTH
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

# Phân quyền cho web server (nếu cần)
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

EXPOSE 9000

CMD ["php-fpm"]
