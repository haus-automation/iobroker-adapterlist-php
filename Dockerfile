FROM php:8.3-apache

RUN echo "Europe/Berlin" > /etc/timezone && dpkg-reconfigure -f noninteractive tzdata

RUN apt-get update && apt-get upgrade -y && apt-get install -y git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.* .

RUN composer install --no-dev
