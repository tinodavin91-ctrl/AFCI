FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts

# Create SQLite database & grant full write permissions to storage, cache, and database
RUN touch database/database.sqlite \
    && chmod -R 777 database storage bootstrap/cache

EXPOSE 8080

# Generate key if missing, run migrations + seeders, and start the server
CMD ["sh", "-c", "php artisan key:generate --force && php artisan config:clear && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host 0.0.0.0 --port ${PORT:-8080}"]
