FROM php:7-apache
RUN apt-get update && apt-get install -y libzip-dev
RUN docker-php-ext-install pdo pdo_mysql zip
RUN a2enmod rewrite
RUN service apache2 restart

# Install FFMPEG
RUN apt-get update && apt-get install -y ffmpeg

# Install VobSub2SRT
RUN add-apt-repository ppa:ruediger-c-plusplus/vobsub2srt
RUN apt-get update
RUN apt-get install vobsub2srt