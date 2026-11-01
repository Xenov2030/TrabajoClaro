FROM composer:2 AS composer
FROM php:8.4-cli-bookworm
RUN --mount=type=secret,id=proxy_ca,target=/run/proxy-ca,required=false \
    if [ -f /run/proxy-ca ]; then cp /run/proxy-ca /usr/local/share/ca-certificates/session-proxy.crt; update-ca-certificates; fi; \
    apt-get update && apt-get install -y --no-install-recommends git unzip libzip-dev libonig-dev libxml2-dev libicu-dev libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite bcmath mbstring zip intl \
    && rm -f /usr/local/share/ca-certificates/session-proxy.crt && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
EXPOSE 8000
CMD ["bash", "scripts/serve.sh"]
