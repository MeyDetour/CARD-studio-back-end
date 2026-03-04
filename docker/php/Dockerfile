FROM php:8.3-apache

# Active les modules Apache nécessaires
RUN a2enmod rewrite

# Installe les extensions PHP utiles pour Symfony et PostgreSQL
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql && \
    rm -rf /var/lib/apt/lists/*

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copie la config Apache
COPY ../apache/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
