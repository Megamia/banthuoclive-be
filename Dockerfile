FROM php:8.2-fpm

# Cài các gói cần thiết
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
    supervisor

# Cài các extension PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php \
    -- --install-dir=/usr/local/bin --filename=composer

# Tạo thư mục làm việc
WORKDIR /var/www

# Copy mã nguồn vào container
COPY . .

# Cài các package PHP (OctoberCMS)
RUN mkdir -p /root/.composer && \
    echo '{"http-basic":{"gateway.octobercms.com":{"username":"luudat214@gmail.com","password":"0AQD4AmHgZwxkAGZ4YGIzLGAwAGDlZwp4AQx0MQAxAJIuBTL3LmplLwVkAQL3"}}}' \
    > /root/.composer/auth.json && \
    composer install --ignore-platform-reqs --no-interaction --prefer-dist && \
    rm /root/.composer/auth.json

# Copy file cấu hình nginx và supervisor
COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisord.conf

# Railway yêu cầu expose port 8080
EXPOSE 8080

# Chạy nginx + php-fpm bằng supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
