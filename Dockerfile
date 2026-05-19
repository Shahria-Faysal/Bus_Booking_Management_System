FROM php:8.3-cli

# -----------------------------
# System dependencies
# -----------------------------
RUN apt-get update && apt-get install -y \
    git unzip curl libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# PHP extensions
# -----------------------------
RUN docker-php-ext-install pdo pdo_sqlite

# -----------------------------
# Composer
# -----------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# -----------------------------
# Node.js
# -----------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# -----------------------------
# Work directory
# -----------------------------
WORKDIR /app

COPY . .

# -----------------------------
# Create minimal .env for build
# -----------------------------
RUN echo "APP_KEY=base64:qqtf2LSo984FG6QJlmInxZJNFjmZrtD3xffBX5eLKdo=" > .env && \
    echo "DB_CONNECTION=sqlite" >> .env

# -----------------------------
# Install PHP dependencies
# -----------------------------
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# -----------------------------
# Install frontend assets
# -----------------------------
RUN npm install && npm run build

# -----------------------------
# Fix Laravel storage/cache paths (IMPORTANT)
# -----------------------------
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app \
    bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Remove temp .env
RUN rm -f .env

# -----------------------------
# Expose port (Render injects $PORT)
# -----------------------------
EXPOSE $PORT

# -----------------------------
# Copy & use the entrypoint script
# (handles APP_URL, caches, and starts the server)
# -----------------------------
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
