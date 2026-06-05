#!/bin/sh
set -e

echo "=== Golden Bird CRM V7.2 — Render Deploy ==="

export PORT=${PORT:-8080}
echo "→ Listening on port $PORT"

# Ensure required directories exist
mkdir -p /var/www/html/database \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Ensure SQLite file exists
touch /var/www/html/database/database.sqlite
chmod 775 /var/www/html/database/database.sqlite

# Fix ownership
chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/database

# Generate app key if not set
php artisan key:generate --force 2>/dev/null || true

# Cache config & routes (non-fatal — skip view:cache which needs all views compiled)
php artisan config:cache  || echo "⚠ config:cache skipped"
php artisan route:cache   || echo "⚠ route:cache skipped"

# Fix ownership again after cache
chown -R www-data:www-data /var/www/html/bootstrap/cache /var/www/html/storage

# Run migrations
echo "→ Running migrations..."
php artisan migrate --force && echo "✔ Migrations done" || echo "⚠ Migration warning (continuing)"

# Seed data
echo "→ Running seeders..."
php artisan db:seed --force && echo "✔ Seed done" || echo "⚠ Seed warning (continuing)"

# Inject PORT into nginx config
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf
echo "✔ Nginx configured for port $PORT"

# Start PHP-FPM
php-fpm -D
echo "✔ PHP-FPM started"

echo "✔ Starting nginx on port $PORT — app is live!"
exec nginx -g 'daemon off;'
