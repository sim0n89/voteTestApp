# Используем официальный образ PHP с установленным Apache
FROM php:8.1-apache

# Установим необходимые пакеты и расширения PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql

# Установим Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настроим рабочую директорию
WORKDIR /var/www/html

# Скопируем файлы проекта (если не хотим использовать volume .:/var/www/html)
# COPY . /var/www/html

# Если нужно, включим модуль Apache mod_rewrite для Laravel
RUN a2enmod rewrite

# Настраиваем VirtualHost (по умолчанию DocumentRoot /var/www/html/public)
# Можно прописать свои параметры, чтобы Laravel корректно обрабатывал маршруты
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Expose порты (по умолчанию 80 для Apache)
EXPOSE 80

# Стартуем Apache
CMD ["apache2-foreground"]
