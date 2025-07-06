#!/bin/sh

echo "📂 Kiểm tra thư mục volume uploads..."

# Nếu volume /uploads đang trống, thì copy ảnh mẫu vào
if [ -d /var/www/_original_uploads ] && [ "$(ls -A /uploads 2>/dev/null)" = "" ]; then
  echo "📥 Đang ép copy ảnh mẫu vào volume..."
  cp -rn /var/www/_original_uploads/* /uploads/
  echo "✅ Đã copy ảnh mẫu vào volume."
else
  echo "⚠️ Volume đã có ảnh hoặc không tìm thấy ảnh mẫu."
fi

# Chạy Laravel server (bạn có thể thay bằng php-fpm hoặc serve tùy nhu cầu)
php artisan serve --host=0.0.0.0 --port=8080
