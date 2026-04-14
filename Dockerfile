FROM php:8.2-fpm-alpine
RUN apk add --no-cache libldap openldap-dev $PHPIZE_DEPS     && docker-php-ext-configure ldap --with-libdir=lib/     && docker-php-ext-install ldap pdo_mysql     && pecl install redis     && docker-php-ext-enable redis     && apk del $PHPIZE_DEPS
WORKDIR /var/www/html
