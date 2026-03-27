FROM php:8.2-fpm-alpine

WORKDIR /var/www/user

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    pkgconfig \
    openssl-dev \
    make \
    autoconf \
    gcc \
    g++ \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd

# Install MongoDB PHP extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy only the User module
COPY --chown=www-data:www-data . /var/www/user

# Create a minimal Laravel structure for the module
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
RUN chown -R www-data:www-data /var/www/user/bootstrap/cache /var/www/user/storage

# Create minimal composer.json for the module
RUN echo '{"require": {"php": "^8.2", "laravel/framework": "^12.0"}, "autoload": {"psr-4": {"App\\\\Modules\\\\User\\\\": ""}}}' > composer.json

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 9004 for PHP-FPM
EXPOSE 9004

CMD ["php-fpm"]
