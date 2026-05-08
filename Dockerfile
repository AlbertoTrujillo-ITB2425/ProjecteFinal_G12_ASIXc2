FROM php:8.2-fpm-alpine

# 1. Instalación de herramientas de ejecución (Se quedan en la imagen)
RUN apk add --no-cache \
    nmap \
    nmap-scripts \
    curl \
    libpng \
    libssh2 \
    libxml2 \
    libpng-dev \
    libxml2-dev \
    libssh2-dev

# 2. Capa de Compilación (Se instalan, se usan y se BORRAN para ahorrar espacio y tiempo)
RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    g++ \
    make \
    linux-headers \
    && pecl install redis ssh2-1.3.1 \
    && docker-php-ext-enable redis ssh2 \
    && docker-php-ext-install pdo pdo_mysql gd xml \
    && apk del .build-deps 

# 3. Optimización de PHP para producción (Sesiones y Rendimiento)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/exposure_php = On/exposure_php = Off/' "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
