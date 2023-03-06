FROM php:8.1-apache

RUN apt-get upgrade -y
RUN apt-get update
RUN apt-get install -y vim

RUN a2enmod rewrite
COPY src/ /var/www/html/
RUN rm /var/www/html/cache/*
RUN rm /var/www/html/logs/*
RUN chmod 777 /var/www/html/cache
RUN chmod 777 /var/www/html/logs
