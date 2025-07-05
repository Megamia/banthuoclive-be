FROM php:8.2-fpm

# Cài đặt các gói hệ thống cần thiết
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
    && docker-php-ext-install pdo mbstring zip exif pcntl bcmath

# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Thư mục làm việc
WORKDIR /var/www

# Copy mã nguồn vào container
COPY . .

# ✅ Giải mã base64 và tạo auth.json
ARG COMPOSER_AUTH_BASE64
RUN mkdir -p /root/.composer && \
    echo "$COMPOSER_AUTH_BASE64" | base64 -d > /root/.composer/auth.json && \
    cat /root/.composer/auth.json

# ✅ Truyền license vào env cho OctoberCMS
ARG OCTOBER_LICENSE
ENV OCTOBER_LICENSE=$OCTOBER_LICENSE

# ✅ Cài các thư viện PHP
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

# Cài frontend nếu có
RUN npm install && npm run build

# Expose port
EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
