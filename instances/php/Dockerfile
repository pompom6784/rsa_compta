FROM php:8.3-alpine

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions opcache intl zip gd xml;
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"