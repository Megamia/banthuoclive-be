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

# Sao lưu ảnh gốc nếu tồn tại
RUN mkdir -p /var/www/_original_uploads && \
    if [ -d "storage/app/uploads/public" ]; then \
    cp -r storage/app/uploads/public /var/www/_original_uploads/; \
    echo "✅ Đã sao lưu ảnh mẫu."; \
    else \
    echo "⚠️  Không tìm thấy thư mục ảnh mẫu, bỏ qua."; \
    fi

# Cài đặt Composer (ẩn token)
RUN mkdir -p /root/.composer && \
    sh -c 'echo "$COMPOSER_AUTH"' > /root/.composer/auth.json && \
    composer install --no-interaction --prefer-dist --no-dev --quiet && \
    rm /root/.composer/auth.json

EXPOSE 8000

# Start script
CMD ["sh", "-c", "\
    echo '📂 Kiểm tra thư mục volume uploads...' && \
    mkdir -p /var/www/storage/app/uploads/public && \
    echo '📥 Đang ép copy ảnh mẫu vào volume...' && \
    echo '📂 Ảnh mẫu có trong _original_uploads:' && ls -lR /var/www/_original_uploads/public && \
    if [ -d /var/www/_original_uploads/public ]; then \
    cp -a /var/www/_original_uploads/public/. /var/www/storage/app/uploads/public/ && \
    echo '✅ Đã copy ảnh mẫu vào volume.'; \
    else \
    echo '❌ Không có ảnh mẫu để copy.'; \
    fi; \
    rm -rf /var/www/uploads && \
    ln -s /var/www/storage/app/uploads/public /var/www/uploads && \
    echo '📂 Danh sách ảnh trong /uploads:' && \
    ls -lR /var/www/uploads || echo '❌ Không có ảnh nào!' && \
    php -S 0.0.0.0:8000 -t . \
    "]
