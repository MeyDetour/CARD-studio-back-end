FROM php:8.3-cli-alpine

# 1. Dépendances système et extensions
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

# 3. Copie des fichiers
COPY . /app

# 4. Installation des paquets SANS exécuter les scripts Symfony au build
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# 5. Démarrage du serveur PHP sur le port dynamique Railway
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public/"]