# ─── Base stage ──────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS base

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    zip \
    unzip \
    bash

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        mbstring \
        opcache \
        gd \
        zip \
        bcmath

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ─── Dev stage ────────────────────────────────────────────────────────────────
FROM base AS dev

RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS linux-headers

COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/xdebug.ini  /usr/local/etc/php/conf.d/99-xdebug.ini
COPY docker/php/fpm-dev.conf /usr/local/etc/php-fpm.d/zzz-custom.conf

EXPOSE 9000
CMD ["php-fpm"]

# ─── Deps stage (only prod dependencies) ─────────────────────────────────────
FROM base AS deps

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ─── Prod stage ───────────────────────────────────────────────────────────────
FROM base AS prod

ENV APP_ENV=production

COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/fpm-prod.conf /usr/local/etc/php-fpm.d/zzz-custom.conf

COPY --from=deps /var/www/html /var/www/html

RUN mkdir -p temp log \
    && chown -R www-data:www-data temp log www \
    && chmod -R 775 temp log

EXPOSE 9000
CMD ["php-fpm"]
