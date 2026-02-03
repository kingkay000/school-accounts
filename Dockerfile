FROM php:8.2-fpm

# Install essential system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Install Node.js for asset building
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pdo_sqlite

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files from your branch
COPY . /app

# Install PHP and Node dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN npm install && npm run build

# Setup SQLite database
RUN mkdir -p database && touch database/database.sqlite

# Hugging Face runs as user 1000. Set permissions for storage and database.
RUN chown -R 1000:1000 /app \
    && chmod -R 775 /app/storage \
    && chmod -R 775 /app/bootstrap/cache \
    && chmod 664 database/database.sqlite

# Expose the mandatory Hugging Face port
EXPOSE 7860

# Switch to the non-root user
USER 1000

# Start Laravel directly on port 7860
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=7860"]
