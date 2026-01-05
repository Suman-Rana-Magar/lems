# Use PHP 8.2 with Composer preinstalled
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl libzip-dev libsodium-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache sodium

# Copy Composer from official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Ensure .env exists (fallback for build process only)
RUN php -r "file_exists('.env') || copy('.env.example', '.env');"

# Generate Laravel app key
RUN php artisan key:generate --force

# Expose port
EXPOSE 8000

# Runtime startup commands
CMD php artisan storage:link && \
    php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    (php artisan passport:keys --force || true) && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
