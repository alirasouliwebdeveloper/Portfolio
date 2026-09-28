# syntax=docker/dockerfile:1
# Stages: base (extensions) -> dev (bind-mounted code) | vendor + assets -> production (code baked in).
FROM php:8.5-fpm-alpine AS base

RUN apk add --no-cache \
        bash \
        git \
        curl \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
    && docker-php-ext-install \
        pdo_mysql \
        intl \
        zip \
        gd \
        bcmath \
        exif

# PHP 8.5 bundles Zend OPcache in core (confirmed via `php -m`); a separate
# docker-php-ext-install for it currently fails on this image, see
# https://github.com/docker-library/php/issues/1631

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html


FROM base AS dev

ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} app 2>/dev/null || true \
    && adduser -D -u ${UID} -G app app 2>/dev/null || true

USER app

CMD ["php-fpm"]


FROM base AS vendor
WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY backend/ ./
RUN composer dump-autoload --optimize --no-dev --no-scripts


# The Filament admin theme (light version of the site theme) is compiled with Vite + Tailwind.
FROM node:24-alpine AS assets
WORKDIR /app
COPY backend/package.json backend/package-lock.json ./
RUN npm ci
COPY backend/vite.config.js ./
COPY backend/resources ./resources
COPY backend/app ./app
COPY --from=vendor /app/vendor/filament ./vendor/filament
RUN npm run build


FROM base AS production
COPY docker/php/php.prod.ini /usr/local/etc/php/conf.d/zz-prod.ini
WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app ./
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
# Mock content for the first seed (replace it in the admin afterwards).
COPY seed /var/www/seed
COPY assets/images /var/www/assets/images
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN rm -rf .env tests node_modules \
    && mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && php artisan package:discover --ansi \
    && php artisan filament:assets --ansi \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache public

USER www-data
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
