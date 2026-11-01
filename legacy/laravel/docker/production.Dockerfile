# Preparar primero vendor y public/build con los lockfiles (ver docs/despliegue.md).
FROM trabajoclaro-php AS vendor
WORKDIR /app
COPY . .
RUN test -f public/build/manifest.json && composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

FROM php:8.4-apache-bookworm
RUN --mount=type=secret,id=proxy_ca,target=/run/proxy-ca,required=false \
    if [ -f /run/proxy-ca ]; then cp /run/proxy-ca /usr/local/share/ca-certificates/session-proxy.crt; update-ca-certificates; fi; \
    apt-get update && apt-get install -y --no-install-recommends libonig-dev libicu-dev \
    && docker-php-ext-install -j2 pdo_mysql bcmath mbstring intl \
    && rm -f /usr/local/share/ca-certificates/session-proxy.crt && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app .
COPY docker/apache.production.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.production.ini /usr/local/etc/php/conf.d/production.ini
COPY docker/production-entrypoint.sh /usr/local/bin/trabajoclaro-entrypoint
RUN chmod 755 /usr/local/bin/trabajoclaro-entrypoint && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr SESSION_SECURE_COOKIE=true SESSION_ENCRYPT=true
ENTRYPOINT ["trabajoclaro-entrypoint"]
CMD ["apache2-foreground"]
