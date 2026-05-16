FROM php:8.5-fpm-alpine

# Install Nginx and Supervisor
RUN apk add --no-cache nginx supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first (better caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application code
COPY . .

# Copy Nginx config
COPY nginx.conf /etc/nginx/http.d/default.conf

# Copy Supervisor config
COPY supervisord.conf /etc/supervisord.conf

# Create storage directory if needed
RUN mkdir -p storage && chmod -R 775 storage

# Expose port 80
EXPOSE 80

# Start Supervisor (runs both Nginx and PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]