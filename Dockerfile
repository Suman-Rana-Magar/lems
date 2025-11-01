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

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Ensure .env exists
RUN php -r "file_exists('.env') || copy('.env.example', '.env');"

# Set Railway MySQL env vars in .env
RUN echo "DB_CONNECTION=mysql" >> .env \
    && echo "DB_HOST=mysql.railway.internal" >> .env \
    && echo "DB_PORT=3306" >> .env \
    && echo "DB_DATABASE=railway" >> .env \
    && echo "DB_USERNAME=root" >> .env \
    && echo "DB_PASSWORD=wWxbltuQLpaJiKCwcWhFxzayJmFgtOCm" >> .env

# Generate Laravel app key
RUN php artisan key:generate --force

# Run Laravel caches and create storage symlink
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan storage:link

# Passport setup (without triggering migrations)
RUN php artisan passport:keys --force \
    && php artisan passport:client --personal --name="Personal Access Client" --no-interaction

# Expose the port Railway assigns
EXPOSE 8000

# Start Laravel server
CMD php artisan serve --host=0.0.0.0 --port=$PORT
