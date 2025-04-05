FROM yiisoftware/yii2-php:8.2-apache

# Установка дополнительных расширений и зависимостей
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip pdo_mysql

# Настройка PHP
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Configure the root directory for Apache
RUN sed -i -e 's|/app/web|/app/frontend/web|g' /etc/apache2/sites-available/000-default.conf

# Включение mod_rewrite для Apache
RUN a2enmod rewrite

# Установка рабочей директории
WORKDIR /app

# Настройка прав доступа к директориям
RUN chown -R www-data:www-data /app

# Открываем порт 80
EXPOSE 80
