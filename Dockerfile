FROM php:8.2-apache

# 1. Forcer l'utilisation d'un seul MPM (prefork)
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork rewrite

# 2. Installation des dépendances système et extensions PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Modification du DocumentRoot pour Symfony
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Copie du code source dans le conteneur
WORKDIR /var/www/html
COPY . /var/www/html

# 6. Droits d'écriture pour le cache et les logs Symfony
RUN chown -R www-data:www-data /var/www/html/var || true