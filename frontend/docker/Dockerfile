#FROM php:7.4-apache
FROM php:8.1-apache

RUN apt-get upgrade -y
RUN apt-get update -y && \
    apt-get install -y net-tools mailutils \
    apt-utils default-mysql-client git zip unzip curl libzip-dev vim net-tools less libxml2 libxml2-dev cron sqlite3 \
    libpq-dev libonig-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && apt-get clean \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli pdo pdo_mysql mbstring ctype bcmath xml opcache pgsql pdo_pgsql 

RUN pecl install zip && docker-php-ext-enable zip

RUN a2enmod rewrite

COPY ./docker/etc/apache2/ /etc/apache2/

RUN mkdir /var/www/html/front
RUN mkdir /var/www/html/api

ADD ./front/dist /var/www/html/front/
ADD ./front-api /var/www/html/api/

#RUN rm -rfv /var/www/html/api/storage/framework/cache/data/*
#RUN rm -rfv /var/www/html/api/storage/app/export/*
#RUN rm -rfv /var/www/html/api/storage/app/public/*
#RUN rm -f /var/www/html/api/storage/logs/laravel.log

RUN touch /var/www/html/api/storage/logs/laravel.log
RUN chmod -R 777 /var/www/html/api/storage
#RUN rm -fv /var/www/html/api/.env

RUN sed -e "s/http:\/\/localhost:88//g" -i.backup /var/www/html/front/js/*.js


COPY ./docker/docker-php-ext-*.ini /usr/local/etc/php/conf.d/

COPY ./docker/etc/exim4/update-exim4.conf.conf /etc/exim4/
RUN update-exim4.conf

RUN crontab -l -u root | { cat; echo "SHELL=/bin/bash"; } | crontab -u root -
#RUN crontab -l -u root | { cat; echo "0 3 * * * cd /var/www/html/api/ && /usr/local/bin/php -d memory_limit=4G artisan cache:clear >> /dev/null 2>&1"; } | crontab -u root -
RUN crontab -l -u root | { cat; echo "* * * * * cd /var/www/html/api/ && /usr/local/bin/php -d memory_limit=16G artisan schedule:run >> /dev/null 2>&1"; } | crontab -u root -

RUN sed -i '2iservice cron start' /usr/local/bin/docker-php-entrypoint
RUN sed -i '2iprintenv > /etc/environment' /usr/local/bin/docker-php-entrypoint
