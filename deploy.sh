#!/bin/bash
# Server par git pull ke baad chalao — cPanel Terminal se:
#   cd ~/crm.paartech.in && bash deploy.sh
#
# cPanel Git → Deployment → "Run script after deployment" me bhi laga sakte ho.

set -e

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$APP_DIR"

echo "==> Deploy started: $(date)"

# Composer (vendor git me nahi hota)
if command -v composer >/dev/null 2>&1; then
    composer install --optimize-autoloader --no-dev --no-interaction
elif [ -f composer.phar ]; then
    php composer.phar install --optimize-autoloader --no-dev --no-interaction
else
    echo "WARN: composer not found — vendor folder manually check karein"
fi

# Laravel maintenance
php artisan migrate --force
php artisan storage:link 2>/dev/null || true

php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear

# Production cache (optional — error aaye to comment out)
php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> Deploy finished OK"
