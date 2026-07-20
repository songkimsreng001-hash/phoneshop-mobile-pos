FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libpq-dev \
    default-mysql-client \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        intl \
        bcmath \
        exif \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]