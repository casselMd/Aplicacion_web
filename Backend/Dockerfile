FROM php:8.1-apache

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia el proyecto
COPY . /var/www/html/

# Instala extensiones necesarias
RUN docker-php-ext-install mysqli

# Instala dependencias de Composer
WORKDIR /var/www/html
RUN composer install
