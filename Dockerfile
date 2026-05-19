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

# Create .env so artisan commands work during build
RUN echo "APP_KEY=base64:qqtf2LSo984FG6QJlmInxZJNFjmZrtD3xffBX5eLKdo=" > .env && echo "DB_CONNECTION=sqlite" >> .env

# Install Composer deps (skip scripts — env not fully set up yet)
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

RUN npm install && npm run build

RUN touch database/database.sqlite \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

RUN rm .env

EXPOSE $PORT

CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port $PORT
