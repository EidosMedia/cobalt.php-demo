FROM composer as builder
COPY . .
RUN composer install && mv public ../ && rm composer.json && rm composer.lock

FROM php:apache


COPY --from=builder /app /var/www
COPY --from=builder /public /var/www/html

RUN a2enmod rewrite && apt-get update -y && apt-get install -y libxslt1.1 libxslt1-dev && docker-php-ext-install xsl

EXPOSE 80
