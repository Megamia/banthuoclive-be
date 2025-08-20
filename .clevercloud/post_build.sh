
echo "🚀 Running post-build tasks for OctoberCMS..."

echo "🔧 Setting folder permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod -R 775 storage/framework

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
