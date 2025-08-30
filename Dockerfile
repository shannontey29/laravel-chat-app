# # Stage 1: Build frontend assets with Node
# FROM node:18 as nodebuild
# WORKDIR /app
# COPY package*.json ./
# RUN npm install
# COPY resources ./resources
# COPY vite.config.js ./
# RUN npm run build 

# # Stage 2: Get Composer binary
# FROM composer:latest as composer

# # Stage 3: Main PHP/Apache image
# FROM php:8.1-apache

# # Install system dependencies
# RUN apt-get update && apt-get install -y \
#     libpng-dev libonig-dev libxml2-dev zip unzip git curl

# # Install PHP extensions
# RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# # Copy Composer
# COPY --from=composer /usr/bin/composer /usr/bin/composer

# WORKDIR /var/www

# # Copy app source (excluding node_modules if you use .dockerignore)
# COPY . /var/www

# COPY --from=nodebuild /app/public/build /var/www/public/build

# RUN rm -rf /var/www/html && ln -s /var/www/public /var/www/html

# RUN [ ! -f .env ] && cp .env.example .env || true

# # Install PHP dependencies
# RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# # Set permissions
# RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# EXPOSE 80
# CMD ["apache2-foreground"]


FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip git curl nodejs npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Set working directory
WORKDIR /var/www

# Copy app source
COPY . /var/www

# Install PHP dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node dependencies and build assets
RUN npm install && npm run build

# Make Apache serve from Laravel's public directory
RUN rm -rf /var/www/html && ln -s /var/www/public /var/www/html

# Ensure .env exists (for production, mount your real .env or use secrets)
RUN [ ! -f .env ] && cp .env.example .env || true

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]