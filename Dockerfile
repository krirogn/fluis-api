FROM php:7-apache
RUN apt-get update && apt-get install -y libzip-dev git wget apt-utils
RUN docker-php-ext-install pdo pdo_mysql zip
RUN a2enmod rewrite
RUN service apache2 restart

# Install Zip, Nano, FFMPEG, MKVToolNix, MediaInfo and Tesseract OCR
RUN apt-get update && apt-get install -y zip nano ffmpeg mkvtoolnix mediainfo tesseract-ocr

# Install VobSub2SRT
RUN cd /tmp \
    && wget https://www.deb-multimedia.org/pool/main/d/deb-multimedia-keyring/deb-multimedia-keyring_2016.8.1_all.deb \
    && dpkg -i deb-multimedia-keyring_2016.8.1_all.deb

RUN echo "deb http://www.deb-multimedia.org sid main" | tee -a /etc/apt/sources.list.d/multimedia.list
RUN apt-get update && apt-get upgrade -y
RUN apt-get update && apt-get install -y tesseract-ocr-all vobsub2srt