FROM php:8.2-fpm

# Inject ENV từ Railway
ARG OCTOBER_AUTH_JSON
ENV COMPOSER_AUTH=$OCTOBER_AUTH_JSON

# Install hệ thống & PHP extensions
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
    supervisor \
    gettext-base \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set thư mục làm việc
WORKDIR /var/www

# Copy source code
COPY . .

# Cài Composer
RUN mkdir -p /root/.composer \
    && echo "$COMPOSER_AUTH" > /root/.composer/auth.json \
    && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
    && rm /root/.composer/auth.json

RUN sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/www.conf

# Tạo symlink từ public/uploads → storage/app/uploads
RUN mkdir -p public && rm -rf public/uploads && ln -s ../storage/app/uploads public/uploads

# Phân quyền cho Laravel
RUN mkdir -p bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Copy file config nginx và supervisor
COPY docker/nginx.conf /etc/nginx/sites-available/default.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Railway tự động đặt PORT → chúng ta expose 8000 mặc định
EXPOSE 8000

# CMD: thay biến $PORT và start cả nginx + php-fpm
CMD sh -c "\
    echo '🚀 Railway cấp PORT = '$PORT; \
    envsubst '\$PORT' < /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default; \
    echo '📄 Cấu hình nginx thực tế:'; grep listen /etc/nginx/sites-available/default; \
    echo '📡 Các cổng đang lắng nghe:'; ss -tulpn || netstat -tulpn; \
    tail -F /var/log/nginx/error.log /usr/local/var/log/php-fpm.log & \
    /usr/bin/supervisord -n"

