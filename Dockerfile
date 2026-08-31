FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts

# Create SQLite database file if it doesn't exist
RUN touch database/database.sqlite

# Render assigns a dynamic $PORT environment variable
EXPOSE 8080
CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --force && php artisan serve --host 0.0.0.0 --port ${PORT:-8080}"]
