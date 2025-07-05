FROM php:8.2-fpm

# Cài các gói hệ thống cần thiết
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

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Copy mã nguồn dự án
COPY . .

# ✅ Tạo auth.json từ biến môi trường base64
ARG COMPOSER_AUTH_BASE64
RUN mkdir -p /root/.composer && \
    echo "$COMPOSER_AUTH_BASE64" | base64 -d > /root/.composer/auth.json

# ✅ Truyền license OctoberCMS từ biến môi trường
ARG OCTOBER_LICENSE
ENV OCTOBER_LICENSE=${OCTOBER_LICENSE}

# ✅ Cài các thư viện PHP qua Composer
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

# ✅ Cài đặt và build JS (nếu có)
RUN npm install && npm run build

# Mở cổng 8000
EXPOSE 8000

# Chạy Laravel dev server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
