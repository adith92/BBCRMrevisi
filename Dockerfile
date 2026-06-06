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

# System deps + PHP extensions
RUN apk add --no-cache \
        nginx \
        supervisor \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql pdo_mysql \
        mbstring zip gd bcmath \
        opcache tokenizer xml

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
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Nginx config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP-FPM config
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# php.ini production settings
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && echo "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=10000" \
       >> /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 8080

CMD ["/bin/sh", "-c", "\
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]
