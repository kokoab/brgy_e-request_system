FROM php:8.2-fpm-bookworm

# Set working directory
WORKDIR /var/www

# System dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    build-essential \
    locales \
    zip unzip git curl \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    pkg-config \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install gd pdo_mysql mbstring zip exif pcntl \
 && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add non-root user
RUN groupadd -g 1000 www \
 && useradd -u 1000 -ms /bin/bash -g www www

# Copy application files with proper ownership
COPY --chown=www:www . /var/www

# Switch to non-root user
USER www

# Expose port for php-fpm
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
