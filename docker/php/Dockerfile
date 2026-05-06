FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    supervisor \
    netcat-openbsd \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# WHY:
# Entrypoint is copied and made executable at build time,
# while runtime script still re-applies chmod for mounted volumes.
COPY backend/docker/entrypoint.sh /var/www/docker/entrypoint.sh
RUN chmod +x /var/www/docker/entrypoint.sh
