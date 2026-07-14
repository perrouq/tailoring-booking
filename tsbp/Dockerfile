FROM php:8.2-apache

# Install PHP extensions needed by this app (mysqli + pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite (harmless if unused, needed if any .htaccess rewrites exist)
RUN a2enmod rewrite

# Copy app source into Apache's web root
COPY . /var/www/html/

# Make sure the uploads/images directories are writable by the web server
RUN mkdir -p /var/www/html/uploads /var/www/html/images \
       && chown -R www-data:www-data /var/www/html/uploads /var/www/html/images \
       && chmod -R 755 /var/www/html/uploads /var/www/html/images

# Render provides the PORT env var; Apache needs to listen on it
ENV APACHE_LISTEN_PORT=80
EXPOSE 80

CMD ["apache2-foreground"]
