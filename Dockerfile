FROM php:8.4-cli-alpine

WORKDIR /app

RUN apk add --no-cache \
    git \
    unzip \
    postgresql-dev \
    icu-dev \
    bash \
    && docker-php-ext-install \
    pdo_pgsql \
    intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
