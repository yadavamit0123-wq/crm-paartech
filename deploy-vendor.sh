#!/bin/bash
# CRM vendor install — cPanel Terminal mein chalao
# Usage: cd ~/crm.paartech.in && bash deploy-vendor.sh
#
# .env touch nahi karta. Sirf composer download + vendor install.

set -e

cd "$(dirname "$0")"
PROJECT_DIR="$(pwd)"

echo "=== CRM Vendor Install ==="
echo "Folder: $PROJECT_DIR"
echo ""

# PHP dhundho (cPanel mein alag path ho sakta hai)
PHP=""
for cmd in php php82 php81 php8.2 php8.1 php8.3; do
    if command -v "$cmd" >/dev/null 2>&1; then
        PHP="$cmd"
        break
    fi
done

if [ -z "$PHP" ]; then
    echo "ERROR: php command nahi mila!"
    echo "Try: /usr/local/bin/php -v"
    echo "Ya cPanel → Select PHP Version → 8.2 select karo"
    exit 1
fi

echo "PHP: $($PHP -v | head -1)"
echo ""

# Global composer check
if command -v composer >/dev/null 2>&1; then
    echo "Global composer mil gaya: $(composer --version 2>/dev/null || echo 'OK')"
    echo "Global composer use kar rahe hain..."
    composer config audit.block-insecure false 2>/dev/null || true
    composer install --optimize-autoloader --no-dev --no-scripts
else
    echo "Global composer nahi — composer.phar download kar rahe hain..."
    if [ ! -f composer.phar ]; then
        if command -v curl >/dev/null 2>&1; then
            curl -sS https://getcomposer.org/installer | $PHP
        elif command -v wget >/dev/null 2>&1; then
            wget -qO- https://getcomposer.org/installer | $PHP
        else
            echo "ERROR: curl ya wget chahiye composer download ke liye"
            exit 1
        fi
    fi

    if [ ! -f composer.phar ]; then
        echo "ERROR: composer.phar download fail!"
        exit 1
    fi

    echo "composer.phar download OK"
    $PHP composer.phar config audit.block-insecure false
    $PHP composer.phar install --optimize-autoloader --no-dev --no-scripts
fi

echo ""
if [ -f vendor/autoload.php ]; then
    echo "SUCCESS: vendor/autoload.php mil gaya!"
    echo "Ab browser mein kholo: https://crm.paartech.in/repair.php"
    echo "Auto-Fix dabao, phir repair.php delete karo."
else
    echo "FAIL: vendor/autoload.php nahi bana — output upar dekho"
    exit 1
fi
