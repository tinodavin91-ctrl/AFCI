FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libpq-dev sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Create production .env from .env.example with pre-generated APP_KEY
RUN cp .env.example .env \
    && sed -i 's/APP_KEY=/APP_KEY=base64:NfZ\/ahEryTyd8BSRJy3cRfGUnubersxtpk2yqV\/c+H0=/g' .env \
    && sed -i 's/APP_ENV=local/APP_ENV=production/g' .env \
    && sed -i '/^DB_CONNECTION=/d' .env \
    && printf '\nDB_CONNECTION=pgsql\nDB_HOST=ep-morning-waterfall-ae6u63mp-pooler.c-2.us-east-2.aws.neon.tech\nDB_PORT=5432\nDB_DATABASE=neondb\nDB_USERNAME=neondb_owner\nDB_PASSWORD=npg_NhXGigoFl4n9\nDB_SSLMODE=require\n' >> .env \
    && touch database/database.sqlite \
    && chmod -R 777 .env database storage bootstrap/cache

RUN composer install --no-dev --optimize-autoloader --no-scripts

EXPOSE 8080

CMD ["sh", "-c", "php artisan config:clear && php artisan migrate --force && php artisan serve --host 0.0.0.0 --port ${PORT:-8080}"]