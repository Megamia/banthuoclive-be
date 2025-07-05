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

# 👉 KHAI BÁO ARG để nhận auth từ build context
ARG COMPOSER_AUTH

# Gán vào file auth.json bên trong container
RUN mkdir -p /root/.composer \
    && echo "$COMPOSER_AUTH" > /root/.composer/auth.json

# ✅ Chạy composer install
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist \
    && rm -f /root/.composer/auth.json

RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

EXPOSE 9000

CMD ["php-fpm"]
