#!/bin/sh
echo "🚀 Starting application..."
echo "🌐 PORT is: ${PORT:-8080}"

php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
