#!/bin/sh
echo "=== GPI START ==="
echo "PORT=$PORT"
php artisan config:clear
echo "=== config:clear done ==="
PORT="${PORT:-8080}"
echo "=== Serving on $PORT ==="
exec php -S 0.0.0.0:$PORT -t public/
