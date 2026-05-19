FROM php:8.3-cli

# Install system deps
RUN apt-get update && apt-get install -y \
    git unzip curl libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

WORKDIR /app

COPY . .

# Build
RUN composer install --no-interaction --optimize-autoloader --no-dev \
    && npm install && npm run build \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Create SQLite database
RUN touch database/database.sqlite

EXPOSE $PORT

CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port $PORT
