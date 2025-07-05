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

# 👇 Tạo file auth.json chứa thông tin xác thực OctoberCMS (nên dùng biến môi trường ở Railway)
RUN mkdir -p /root/.composer && echo "{
  \"http-basic\": {
    \"gateway.octobercms.com\": {
      \"username\": \"${OCTOBER_USER}\",
      \"password\": \"${OCTOBER_TOKEN}\"
    }
  }
}" > /root/.composer/auth.json



# Install PHP dependencies
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

# Install JS dependencies
RUN npm install && npm run build

EXPOSE 8000

# Run Laravel dev server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
