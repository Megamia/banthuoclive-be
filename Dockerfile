FROM php:8.2-fpm

# Cài đặt các gói hệ thống cần thiết
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

# Set working directory
WORKDIR /var/www

# Copy source code
COPY . .

# Sao lưu ảnh mẫu nếu có
RUN mkdir -p /var/www/_original_uploads && \
    if [ -d "storage/app/uploads/public" ]; then \
        cp -r storage/app/uploads/public/* /var/www/_original_uploads/ && \
        echo "✅ Đã sao lưu ảnh mẫu."; \
    else \
        echo "⚠️  Không tìm thấy thư mục ảnh mẫu, bỏ qua."; \
    fi

# Cài đặt Composer dependencies (nếu cần auth)
RUN mkdir -p /root/.composer && \
    sh -c 'echo "$COMPOSER_AUTH"' > /root/.composer/auth.json && \
    composer install --no-interaction --prefer-dist --no-dev && \
    rm /root/.composer/auth.json

# Copy entrypoint.sh và cấp quyền thực thi
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Dùng entrypoint khi container khởi động
ENTRYPOINT ["/entrypoint.sh"]
