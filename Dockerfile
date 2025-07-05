FROM php:8.2-fpm

# Inject ENV từ Railway (auth composer cho OctoberCMS plugin)
ARG OCTOBER_AUTH_JSON
ENV COMPOSER_AUTH=$OCTOBER_AUTH_JSON

# Cài các gói hệ thống + extension PHP cần thiết
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

# Set thư mục làm việc
WORKDIR /var/www

# Copy mã nguồn vào container
COPY . .

# Cài thư viện PHP thông qua Composer + auth cho plugin OctoberCMS
RUN mkdir -p /root/.composer \
 && echo "$COMPOSER_AUTH" > /root/.composer/auth.json \
 && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
 && rm /root/.composer/auth.json

# Railway sẽ tự gán biến PORT, ta expose cổng đó
EXPOSE ${PORT}

# Chạy Laravel/October bằng built-in server, đúng cổng Railway yêu cầu
CMD php artisan serve --host=0.0.0.0 --port=${PORT}
