#!/bin/sh
set -e
php artisan config:clear
echo "Starting Laravel on port ${PORT:-8080}"
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
