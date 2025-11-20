# Use official PHP with Apache
FROM php:8.2-apache

# Install necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create necessary directories
RUN mkdir -p uploads/rooms uploads/products uploads/profiles img && \
    chmod -R 755 uploads img

# Set timezone
RUN echo "date.timezone = Asia/Bangkok" > /usr/local/etc/php/conf.d/timezone.ini

# Create startup script to handle dynamic PORT
RUN echo '#!/bin/bash\n\
PORT=${PORT:-80}\n\
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf\n\
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf\n\
apache2-foreground\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# Expose port (Render will override this)
EXPOSE ${PORT:-80}

# Start Apache with dynamic port
CMD ["/usr/local/bin/start.sh"]
