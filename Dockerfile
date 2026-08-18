FROM php:8.3-cli-alpine

# 1. Dépendances système et extensions
RUN apk add --no-cache \
    git \
    unzip \
    libpq \
    libpq-dev \
    icu-dev \
    libzip-dev \
    bash \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/include/postgresql/ \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
        opcache
# 2. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 3. Copie du code
COPY . /app

# 4. Installation des dépendances Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# 5. Téléchargement des assets JS (ImportMap) et compilation des assets Symfony
RUN php bin/console importmap:install --no-interaction \
    && php bin/console asset-map:compile --no-interaction || true

# 6. Démarrage de PHP 

CMD ["sh", "-c", "php bin/console doctrine:schema:update --force --no-interaction && php -S 0.0.0.0:${PORT:-8080} -t public/"]