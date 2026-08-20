FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    git \
    unzip \
    libpq \
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

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . /app

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

  RUN php bin/console importmap:install --no-interaction
 
RUN php bin/console asset-map:compile --no-interaction

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public/"]


CMD ["sh", "-c", "mkdir -p config/jwt && php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction && php bin/console doctrine:schema:update --force --no-interaction && php -S 0.0.0.0:${PORT:-8080} -t public/"]