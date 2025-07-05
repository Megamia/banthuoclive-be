FROM php:8.2-fpm

# Cài các package cần thiết
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    curl \
    zip \
    supervisor \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    cron && \
    docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Cài Composer
RUN curl -sS https://getcomposer.org/installer | php \
    -- --install-dir=/usr/local/bin --filename=composer

# Copy project
WORKDIR /var/www
COPY . .

# Cài dependency cho OctoberCMS
RUN mkdir -p /root/.composer && echo '{
  "http-basic": {
    "gateway.octobercms.com": {
      "username": "luudat214@gmail.com",
      "password": "0AQD4AmHgZwxkAGZ4YGIzLGAwAGDlZwp4AQx0MQAxAJIuBTL3LmplLwVkAQL3"
    }
  }
}' > /root/.composer/auth.json && \
composer install --ignore-platform-reqs --no-interaction --prefer-dist && \
rm /root/.composer/auth.json

# Copy cấu hình nginx & supervisord
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord"]
