FROM composer as builder
COPY . .
RUN ls
RUN composer install

FROM php:apache

RUN a2enmod rewrite

COPY --from=builder /app /var/www/html

EXPOSE 80