FROM php:8.3-fpm

RUN apt-get update && \
    apt-get install -y \
    git \
    unzip \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install zip pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY ./yii-application/composer.json ./yii-application/composer.lock* /app/
RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer

COPY ./yii-application /app

RUN mkdir -p /app/runtime /app/web/assets

RUN chown -R www-data:www-data /app/runtime /app/web/assets

RUN composer dump-autoload --no-scripts --no-dev --optimize
