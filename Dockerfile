FROM php:8.2-fpm

# Inject ENV từ Railway
ARG OCTOBER_AUTH_JSON
ENV COMPOSER_AUTH=$OCTOBER_AUTH_JSON

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
    cron \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy source code
COPY . .

# Sao lưu ảnh gốc sang thư mục tạm trong image
RUN mkdir -p /var/www/_original_uploads \
 && cp -r storage/app/uploads/public /var/www/_original_uploads/

# Cài đặt Composer
RUN mkdir -p /root/.composer \
 && echo "$COMPOSER_AUTH" > /root/.composer/auth.json \
 && composer install --ignore-platform-reqs --no-interaction --prefer-dist \
 && rm /root/.composer/auth.json

EXPOSE 8000

# Start script
CMD ["sh", "-c", "\
  echo '📂 Kiểm tra thư mục volume uploads...' && \
  mkdir -p /var/www/storage/app/uploads/public && \
  if [ -z \"$(ls -A /var/www/storage/app/uploads/public 2>/dev/null)\" ]; then \
    echo '📥 Volume đang trống, đang copy ảnh mẫu...' && \
    cp -r /var/www/_original_uploads/public/* /var/www/storage/app/uploads/public/; \
  else \
    echo '✅ Volume đã có dữ liệu'; \
  fi && \
  mkdir -p /var/www/public && \
  rm -rf /var/www/public/uploads && \
  ln -s /var/www/storage/app/uploads/public /var/www/public/uploads && \
  echo '📂 Danh sách ảnh trong /public/uploads:' && \
  ls -R /var/www/public/uploads && \
  php -S 0.0.0.0:8000 -t public \
"]
