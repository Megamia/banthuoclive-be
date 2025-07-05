FROM php:8.2-fpm

# Inject ENV từ Railway
ARG OCTOBER_AUTH_JSON
ENV COMPOSER_AUTH=$OCTOBER_AUTH_JSON

# Install packages
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
    supervisor \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy source
COPY . .

# Composer install
RUN mkdir -p /root/.composer \
 && printf '%s' "$COMPOSER_AUTH" > /root/.composer/auth.json \
 && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
 && rm /root/.composer/auth.json

# Set permission
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data /var/www \
 && chmod -R 775 storage bootstrap/cache

# Tạo thư mục log cho nginx (ngừa lỗi 502)
RUN mkdir -p /var/log/nginx

# Copy nginx + supervisord config
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Clear Laravel/October cache
RUN php artisan config:clear \
 && php artisan cache:clear \
 && php artisan view:clear || true  # Cho phép tiếp tục nếu chưa đủ ENV

# Tùy chọn: migrate nếu đã config DB (không bắt buộc)
# RUN php artisan migrate --force || true

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-n"]
