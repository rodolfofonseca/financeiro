FROM php:8.4-apache

WORKDIR /var/www/html

RUN a2enmod rewrite headers expires \
  && apt-get update \
  && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    gd \
    mbstring \
    opcache \
    zip \
  && pecl install mongodb \
  && docker-php-ext-enable mongodb \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

