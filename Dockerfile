FROM php:8.2-fpm

# Install system packages
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
    zip \
    cron \
    && docker-php-ext-install pdo mbstring zip exif pcntl bcmath

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy source code
COPY . .

# ✅ Tạo auth.json từ biến môi trường base64
ARG COMPOSER_AUTH_BASE64
ENV COMPOSER_AUTH_BASE64=$COMPOSER_AUTH_BASE64
RUN mkdir -p /root/.composer && \
    echo "$COMPOSER_AUTH_BASE64" | base64 -d > /root/.composer/auth.json

# ✅ Truyền license vào container
ARG OCTOBER_LICENSE
ENV OCTOBER_LICENSE=${OCTOBER_LICENSE}

# Install PHP dependencies
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

# Install JS dependencies
RUN npm install && npm run build

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
