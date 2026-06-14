# ── Stage 1: Node build (Vite assets) ─────────────────────────────────────────
FROM node:22-alpine AS node-builder

WORKDIR /app
COPY package*.json ./
RUN npm ci

COPY resources/ resources/
COPY vite.config.js tailwind.config.js postcss.config.js* ./
RUN npm run build

# ── Stage 2: PHP production image ─────────────────────────────────────────────
FROM php:8.4-fpm-alpine

# System deps + PHP extensions (SQLite only — no PostgreSQL/MySQL needed)
RUN apk add --no-cache \
        nginx \
        supervisor \
        sqlite-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_sqlite \
        mbstring zip bcmath opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application
COPY . .

# Copy built Vite assets from node-builder stage
COPY --from=node-builder /app/public/build public/build

# Install PHP dependencies (no dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN mkdir -p storage bootstrap/cache database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database \
    && chmod 664 database/database.sqlite

# Nginx config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP-FPM config
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# php.ini production settings
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && printf "opcache.enable=1\nopcache.memory_consumption=256\nopcache.interned_strings_buffer=16\nopcache.max_accelerated_files=20000\nopcache.validate_timestamps=0\nopcache.save_comments=1\nopcache.enable_file_override=1\n" \
       >> /usr/local/etc/php/conf.d/opcache.ini \
    && printf "realpath_cache_size=4096K\nrealpath_cache_ttl=600\n" \
       >> /usr/local/etc/php/conf.d/realpath.ini

EXPOSE 8080

CMD ["/bin/sh", "-c", "\
 mkdir -p /var/www/html/storage/database \
 /var/www/html/storage/app/public \
 /var/www/html/storage/framework/cache/data \
 /var/www/html/storage/framework/sessions \
 /var/www/html/storage/framework/views \
 /var/www/html/storage/logs \
 /var/www/html/bootstrap/cache && \
 touch /var/www/html/storage/database/database.sqlite && \
 chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
 chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
 chmod 664 /var/www/html/storage/database/database.sqlite && \
 php artisan migrate --force && \
 php artisan config:cache && \
 php artisan route:cache && \
 php artisan view:cache && \
 exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]
