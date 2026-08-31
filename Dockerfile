FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Ensure .env exists by copying .env.example, create SQLite DB, and set write permissions
RUN cp .env.example .env \
    && touch database/database.sqlite \
    && chmod -R 777 .env database storage bootstrap/cache

RUN composer install --no-dev --optimize-autoloader --no-scripts

EXPOSE 8080

# Auto-generate key into .env, clear config, migrate, seed, and start server
CMD ["sh", "-c", "php artisan key:generate --force && php artisan config:clear && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host 0.0.0.0 --port ${PORT:-8080}"]
