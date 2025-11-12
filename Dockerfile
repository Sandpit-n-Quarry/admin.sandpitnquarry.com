FROM php:8.3-apache

# Install system dependencies
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libicu-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    gd \
    intl \
    pdo_mysql \
    pgsql \
    pdo_pgsql \
    zip

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Create a custom virtual host configuration for Cloud Run
# Use port 8080 directly in the Apache config since Cloud Run expects this port
RUN { \
      echo '<VirtualHost *:8080>' ;\
      echo '  DocumentRoot /var/www/html/public' ;\
      echo '  <Directory /var/www/html/public>' ;\
      echo '    AllowOverride All' ;\
      echo '    Require all granted' ;\
      echo '  </Directory>' ;\
      echo '  ErrorLog ${APACHE_LOG_DIR}/error.log' ;\
      echo '  CustomLog ${APACHE_LOG_DIR}/access.log combined' ;\
      echo '</VirtualHost>' ;\
    } > /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && find /var/www/html/public/assets -type d -exec chmod 755 {} \; \
    && find /var/www/html/public/assets -type f -exec chmod 644 {} \;

# Install dependencies
RUN composer install --optimize-autoloader --no-interaction --no-plugins --no-scripts --prefer-dist
RUN npm install && npm run build

# Copy entrypoint script and set permissions
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Install diagnostic tools
RUN apt-get update && apt-get install -y net-tools procps lsof

# Configure for Cloud Run - explicitly set port 8080
ENV PORT=8080

# Expose port 8080 explicitly for Cloud Run
EXPOSE 8080

# Set Apache environment variables
ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data
ENV APACHE_PID_FILE=/var/run/apache2/apache2.pid
ENV APACHE_LOG_DIR=/var/log/apache2
ENV APACHE_LOCK_DIR=/var/lock/apache2

# Set the entrypoint script
ENTRYPOINT ["docker-entrypoint.sh"]
