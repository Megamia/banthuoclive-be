#!/bin/sh

echo "📢 PORT is: ${PORT:-8080}"
echo "🌐 Application running at http://0.0.0.0:${PORT:-8080}"

php artisan serve --host=0.0.0.0 --port=${PORT:-8080} -v

