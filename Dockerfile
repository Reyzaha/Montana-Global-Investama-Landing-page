FROM php:8.2-apache

# Install dependencies and PHP extensions for MySQL PDO
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql opcache \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache Rewrite & Headers Module
RUN a2enmod rewrite headers

# Configure Apache DocumentRoot and Permissions
WORKDIR /var/www/html
COPY . /var/www/html/

# Set security file permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose HTTP port
EXPOSE 80

CMD ["apache2-foreground"]
