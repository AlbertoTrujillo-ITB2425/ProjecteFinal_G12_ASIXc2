FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nmap \
    nmap-scripts \
    curl \
    libpng-dev \
    libxml2-dev \
    libssh2-dev \
    autoconf \
    g++ \
    make \
    linux-headers

RUN pecl install redis ssh2-1.3.1 \
    && docker-php-ext-enable redis ssh2

RUN docker-php-ext-install pdo pdo_mysql gd xml

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
