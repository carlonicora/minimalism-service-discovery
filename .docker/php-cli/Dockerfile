FROM php:8.2-cli-alpine

RUN set -xe  \
    && apk add --update linux-headers --no-cache --virtual .build-deps $PHPIZE_DEPS bash git supervisor curl g++ make autoconf

RUN docker-php-ext-install mysqli bcmath opcache \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && export COMPOSER_PROCESS_TIMEOUT=600 \
    && pecl install redis \
    && docker-php-ext-enable redis