FROM php:8.2-cli-alpine

# 1. Installation des dépendances système et extensions PostgreSQL / Symfony
RUN apk add --no-cache \
    git \
    unzip \
    libpq-dev \
    icu-dev \
    libzip-dev \
    bash \
    curl \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
        opcache

# 2. Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Installation du binaire Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR /app

# 4. Copie du projet
COPY . /app

# 5. Installation des dépendances Composer (sans dev en prod)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev || true

# 6. Démarrage du serveur Symfony en écoute sur 0.0.0.0 et le port Railway ($PORT)
CMD ["sh", "-c", "symfony server:start --no-tls --port=${PORT:-8080} --allow-all-ip"]