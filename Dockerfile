FROM php:7-apache
RUN apt-get update && apt-get install -y libzip-dev git
RUN docker-php-ext-install pdo pdo_mysql zip
RUN a2enmod rewrite
RUN service apache2 restart

# Install FFMPEG
RUN apt-get update && apt-get install -y ffmpeg

# Build VobSub2SRT
RUN apt-get update \
    && apt-get install -y libtiff5-dev libtesseract-dev tesseract-ocr-all build-essential cmake pkg-config \
    && apt-get clean \
    && git clone https://github.com/ruediger/VobSub2SRT.git VobSub2SRT \
    && cd VobSub2SRT \
    && ./configure \
    && make -j`nproc` \
    && make install \
    && make clean