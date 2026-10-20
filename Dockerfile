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

# Enable Apache Rewrite, Headers & RemoteIP Module (for real client IP behind Nginx)
RUN a2enmod rewrite headers remoteip \
    && echo 'RemoteIPHeader X-Forwarded-For' > /etc/apache2/conf-available/remoteip.conf \
    && echo 'RemoteIPInternalProxy 10.0.0.0/8 172.16.0.0/12 192.168.0.0/16 127.0.0.1' >> /etc/apache2/conf-available/remoteip.conf \
    && a2enconf remoteip

# Configure Apache DocumentRoot and Permissions
WORKDIR /var/www/html
COPY . /var/www/html/

# Set security file permissions and entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose HTTP port
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
