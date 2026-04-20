FROM php:8.2-fpm-alpine

# Instalación de herramientas de auditoría, librerías de sistema y dependencias de compilación
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

# Instalación y habilitación de extensiones PECL (Redis y SSH2)
RUN pecl install redis ssh2-1.3.1 \
    && docker-php-ext-enable redis ssh2

# Instalación de extensiones nativas de PHP (MySQL, GD para imágenes y XML)
RUN docker-php-ext-install pdo pdo_mysql gd xml

# Configuración del directorio de trabajo
WORKDIR /var/www/html

# Exposición del puerto por defecto de PHP-FPM
EXPOSE 9000

# Comando para iniciar el servicio
CMD ["php-fpm"]
