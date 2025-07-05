FROM php:8.2-fpm

# Inject ENV từ Railway
ARG OCTOBER_AUTH_JSON
ENV COMPOSER_AUTH=$OCTOBER_AUTH_JSON

# Cài gói cần thiết
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
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Làm việc tại đây
WORKDIR /var/www

# Copy mã nguồn
COPY . .

# Cài Composer (bỏ qua platform req)
RUN mkdir -p /root/.composer \
 && echo "$COMPOSER_AUTH" > /root/.composer/auth.json \
 && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
 && rm /root/.composer/auth.json

# Copy file nginx config vào đúng chỗ
COPY nginx.conf /etc/nginx/sites-available/default

# Link storage
RUN php artisan storage:link || true

# Copy Supervisor config để chạy cả nginx & php-fpm
COPY supervisord.conf /etc/supervisord.conf

EXPOSE 80

# Start bằng Supervisor (chạy cả nginx và php-fpm)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
