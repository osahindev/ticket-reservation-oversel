FROM dunglas/frankenphp

COPY . /app

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    pdo_pgsql


ENV SERVER_NAME=:80