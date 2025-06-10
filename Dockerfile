FROM php:8.2-apache

# Instale dependências do sistema e extensões necessárias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql zip gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


# Habilite mod_rewrite do Apache
RUN a2enmod rewrite

# Instale o Composer (oficial)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Defina o diretório de trabalho
WORKDIR /var/www/html

# Copie configurações do PHP e Apache
COPY ./php/config/php.ini /usr/local/etc/php/
COPY ./php/config/apache.conf /etc/apache2/sites-available/000-default.conf

# Copie composer.json e composer.lock
COPY composer.json composer.lock ./

# Instale dependências do Composer (TCPDF, etc)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copie o código-fonte da aplicação
COPY ./src/ /var/www/html/

