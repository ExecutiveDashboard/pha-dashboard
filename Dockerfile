FROM php:8.2-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend assets
RUN npm ci || npm install
RUN npm run build

# Set permissions for storage, bootstrap/cache, and SQLite database
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && touch /var/www/database/database.sqlite \
    && chmod 777 /var/www/database/database.sqlite \
    && chmod 777 /var/www/database

# Optimize Laravel configuration for production
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose port for cloud environment
EXPOSE 8000

# Start command: run migrations and start Laravel development/production server on dynamic PORT
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
