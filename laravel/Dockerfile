# ==============================================================================
# Stage 1: Build frontend assets (Vite, Vue 3, Bootstrap 5)
# ==============================================================================
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

# ==============================================================================
# Stage 2: Production PHP 8.4 FPM + Nginx (ServerSideUp hardened for Laravel)
# ==============================================================================
FROM serversideup/php:8.4-fpm-nginx

ENV AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION=true \
    AUTORUN_LARAVEL_MIGRATION_SEED=true \
    AUTORUN_LARAVEL_STORAGE_LINK=true \
    AUTORUN_LARAVEL_OPTIMIZE=true \
    PHP_OPCACHE_ENABLE=1

WORKDIR /var/www/html

USER root

# Install composer dependencies
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy full application code and frontend build artifacts
COPY --chown=www-data:www-data . .
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-scripts && \
    chmod -R 775 storage bootstrap/cache

USER www-data
