FROM php:8.3-cli-alpine

# 1. Extensions et paquets nécessaires
RUN apk add --no-cache \
    git \
    unzip \
    libpq-dev \
    icu-dev \
    libzip-dev \
    bash \
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

# 3. Copie du projet
COPY . /app

# 4. Installation complète des dépendances et génération de l'autoloader
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
    && composer dump-autoload --optimize

# 5. Démarrage de PHP sur le port dynamique Railway
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public/"]