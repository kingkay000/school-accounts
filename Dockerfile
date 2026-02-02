FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nginx \
    gnupg

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo_sqlite

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Build assets
RUN npm install && npm run build

# Setup SQLite
RUN mkdir -p database && touch database/database.sqlite

# IMPORTANT: Hugging Face runs as user 1000
RUN chown -R 1000:1000 /app \
    && chmod -R 775 /app/storage \
    && chmod -R 775 /app/bootstrap/cache \
    && chmod 664 database/database.sqlite

# Nginx configuration
COPY nginx.conf /etc/nginx/sites-available/default

# Expose port 7860
EXPOSE 7860

# Start script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Switch to the Hugging Face user
USER 1000

CMD ["/start.sh"]
