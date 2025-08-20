#!/bin/bash

echo "🚀 Running post-build tasks for OctoberCMS..."

# Tạo folder nếu chưa có
mkdir -p storage/framework bootstrap/cache

# Set quyền
chmod -R 775 storage bootstrap/cache

# Tạo .htaccess nếu chưa có
HTACCESS_FILE="./.htaccess"
if [ ! -f "$HTACCESS_FILE" ]; then
    echo "📄 Creating .htaccess for Laravel routing..."
    cat <<EOL > $HTACCESS_FILE
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
EOL
fi

echo "✅ Post-build tasks finished."
