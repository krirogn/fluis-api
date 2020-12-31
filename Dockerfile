FROM php:7-apache
RUN apt-get update && apt-get install -y libzip-dev
RUN docker-php-ext-install pdo pdo_mysql zip
RUN a2enmod rewrite
RUN service apache2 restart
