FROM php:8.1-apache

# ─── Sistema y extensiones PHP ───────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install intl mysqli pdo pdo_mysql opcache

# ─── Apache: rewrite + headers + KeepAlive ───────────────────────────────────
RUN a2enmod rewrite headers expires

# ─── PHP custom config (opcache + JIT + realpath cache) ──────────────────────
COPY docker/php-pinca.ini /usr/local/etc/php/conf.d/zz-pinca.ini

# ─── Composer ────────────────────────────────────────────────────────────────
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ─── App ─────────────────────────────────────────────────────────────────────
WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

EXPOSE 80
