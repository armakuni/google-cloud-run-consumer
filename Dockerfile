FROM php:7.3-cli as opencensus
## Get Opencensus Dependenices
RUN apt-get update && apt-get install -y git build-essential && rm -rf /var/cache/apt/
RUN git clone https://github.com/census-instrumentation/opencensus-php.git /extension
WORKDIR /extension/ext
## Build Opencensus Extension
RUN phpize \
    && ./configure \
    && make

FROM composer as composer

## Enable GRPC
RUN apk --no-cache add alpine-sdk autoconf zlib-dev zlib \
  && pecl install grpc \
  && docker-php-ext-enable grpc \
  && apk del alpine-sdk autoconf zlib-dev

## Enable Opcache
RUN docker-php-ext-enable opcache

## Enable Opencensus Extension
COPY --from=opencensus /extension/ext/modules/opencensus.so /usr/local/lib/php/extensions/no-debug-non-zts-20180731/opencensus.so
RUN docker-php-ext-enable opencensus

## Get dependencies and perform any preprocessing
COPY . /app
RUN composer install -o -a --apcu-autoloader

FROM php:7.3-apache

## Read port from env
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

## Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

## Enable GRPC
RUN apt-get update \
  && apt-get install -y zlib1g-dev \
  && pecl install grpc \
  && apt-get remove -y zlib1g-dev \
  && rm -rf /var/cache/apt/ \
  && docker-php-ext-enable grpc

## Enable Opcache
RUN docker-php-ext-enable opcache

## Enable Opencensus Extension
COPY --from=opencensus /extension/ext/modules/opencensus.so /usr/local/lib/php/extensions/no-debug-non-zts-20180731/opencensus.so
RUN docker-php-ext-enable opencensus

## Copy the App
COPY --from=composer /app /var/www/html/
RUN chown -R www-data:www-data /var/www/html/
