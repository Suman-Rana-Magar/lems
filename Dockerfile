# Use PHP 8.3 with Composer preinstalled
FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Copy Composer from official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy app files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Generate Laravel app key if not present
RUN php artisan key:generate --force || true

# Expose port 8000
EXPOSE 8000

# Start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=$PORT
