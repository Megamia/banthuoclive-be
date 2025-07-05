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
    cron \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working dir
WORKDIR /var/www

# Copy code và .env
COPY . .

# Tạo auth.json rồi cài composer
RUN mkdir -p /root/.composer \
 && echo "$COMPOSER_AUTH" > /root/.composer/auth.json \
 && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
 && rm /root/.composer/auth.json

# ⚠️ Clear và cache config (phải copy .env trước bước này)
RUN php artisan config:clear && php artisan config:cache

# Mở port
EXPOSE ${PORT}

# ✅ Run server đúng cách với Railway
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT}"]
