FROM php:8.2-fpm

ARG OCTOBER_AUTH_JSON
ENV COMPOSER_AUTH=$OCTOBER_AUTH_JSON

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

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

COPY . .

# ✅ Xoá thư mục uploads cũ (nếu có), tạo symlink chuẩn
RUN rm -rf public/uploads && ln -s ../storage/app/uploads public/uploads

# ✅ Phân quyền cho Laravel ghi logs, upload file
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# ✅ Cài đặt Composer
RUN mkdir -p /root/.composer \
 && echo "$COMPOSER_AUTH" > /root/.composer/auth.json \
 && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
 && rm /root/.composer/auth.json

EXPOSE 8000

CMD ["sh", "-c", "sleep 10 && php artisan serve --host=0.0.0.0 --port=8000"]
