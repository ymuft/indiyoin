FROM php:8.3-apache
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl git unzip libsqlite3-dev libzip-dev libonig-dev libxml2-dev libpng-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring zip gd dom simplexml xml xmlreader xmlwriter \
    && a2enmod headers expires \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/apache2/sites-enabled/000-default.conf \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf '%s\n' 'expose_php=Off' 'display_errors=Off' 'log_errors=On' 'upload_max_filesize=32M' 'post_max_size=34M' > "$PHP_INI_DIR/conf.d/indiyoin.ini"
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p storage/imports storage/analysis \
    && chown -R www-data:www-data storage
COPY docker/apache.conf /etc/apache2/sites-enabled/indiyoin.conf
EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD curl -fsS http://127.0.0.1/health || exit 1
