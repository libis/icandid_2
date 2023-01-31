FROM php:7.2-apache
RUN a2enmod rewrite
COPY src/ /var/www/html/
RUN rm /var/www/html/cache/*
RUN chmod 777 /var/www/html/cache
RUN chmod 777 /var/www/html/logs
