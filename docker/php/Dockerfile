FROM dunglas/frankenphp:1.9-php8.4-alpine


RUN install-php-extensions \
    ctype \
    curl \
    dom \
    fileinfo \
    filter \
    hash \
    mbstring \
    openssl \
    pcre \
    pdo \
    session \
    tokenizer \
    xml \
    pdo_mysql \
    intl \
    opcache

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
# Add DB CLI tools used by the app: mysqldump, mysql, pg_dump, psql
RUN apk add --no-cache \
        mariadb-client mariadb-connector-c \
        postgresql-client

ENV USER_ID=1000
ENV USER=application

RUN adduser -u ${USER_ID} -D -h /home/${USER} ${USER} && \
    chown -R ${USER}:${USER} /data/caddy && chown -R ${USER}:${USER} /config/caddy

RUN mkdir -p /app && chown -R ${USER}:${USER} /app && chmod -R 775 /app
WORKDIR /app
USER 1000
