FROM php:8.2-apache

# Install dependencies for curl and extensions
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libonig-dev \
    && docker-php-ext-install curl mbstring fileinfo \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable rewrite and allow .htaccess
RUN a2enmod rewrite \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy all application files
COPY . /var/www/html/

# Permissions for data directory
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html/data

ENV PORT=10000
EXPOSE 10000

# Start script: dynamically bind Apache to Render's assigned PORT
CMD sh -c "sed -i \"s/80/\${PORT:-10000}/g\" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && apache2-foreground"
