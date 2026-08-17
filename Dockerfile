FROM php:8.2-apache

# 1. Dépendances système et extensions PHP
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

# 2. Correction radicale des MPM : suppression physique des liens symboliques superflus
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/ \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/ \
    && a2enmod rewrite

# 3. DocumentRoot pour Symfony
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html

# 5. Droits et commande de démarrage explicite
RUN chown -R www-data:www-data /var/www/html/var || true

# Lancement officiel d'Apache pour Debian/Ubuntu
CMD ["apache2-foreground"]