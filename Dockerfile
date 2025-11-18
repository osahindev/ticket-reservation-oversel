FROM composer:2 AS composer_deps
WORKDIR /app
# Sadece composer manifestlerini kopyala ve bağımlılıkları kur (composer.lock varsa birlikte kopyalanır)
COPY composer.* /app/
RUN composer install --prefer-dist --no-interaction --no-scripts --optimize-autoloader

FROM dunglas/frankenphp AS app
WORKDIR /app

# PHP uzantılarını yükle
COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_pgsql

# Composer ve sistem bağımlılıkları (isteğe bağlı: container içinde composer kullanmak için)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Uygulama dosyalarını kopyala
COPY . /app

# Build aşamasında indirilen vendor'ı kopyala (host'taki vendor'ı ezmemek için .dockerignore önerilir)
COPY --from=composer_deps /app/vendor /app/vendor

ENV SERVER_NAME=:80