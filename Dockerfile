# syntax=docker/dockerfile:1.7

# ---- Stage 1: build front-end assets ----
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json vite.config.js ./
COPY resources resources
COPY public public
RUN npm ci --no-audit --no-fund && npm run build

# ---- Stage 2: install PHP dependencies ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
# This example consumes vandetho/symflow-laravel as a Composer **path repo** for
# local development. For the Docker image we want the published Packagist
# release instead, so rewrite composer.json on the fly and discard the lockfile
# (Packagist resolution would not match the path-repo lock).
RUN php -r '$j=json_decode(file_get_contents("composer.json"),true);unset($j["repositories"]);$j["require"]["vandetho/symflow-laravel"]="*";file_put_contents("composer.json",json_encode($j,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));'
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist --ignore-platform-reqs

# ---- Stage 3: runtime ----
FROM dunglas/frankenphp:1-php8.3 AS runtime
WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends sqlite3 \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions intl pdo_sqlite opcache

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

# Use the rewritten composer.json from vendor stage (Packagist-only, no path repo)
COPY --from=vendor /app/composer.json ./composer.json

# FrankenPHP: write a Laravel-friendly Caddyfile that serves /app/public on :8080
RUN printf '{\n\tfrankenphp\n\torder php_server before file_server\n}\n\n:8080 {\n\troot * /app/public\n\tencode zstd br gzip\n\tphp_server\n}\n' > /etc/caddy/Caddyfile

RUN chown -R www-data:www-data storage bootstrap/cache database

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/data/database.sqlite \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database

EXPOSE 8080

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
