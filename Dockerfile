FROM php:8.2-apache

# Instalar dependencias del sistema necesarias para GD
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libgif-dev \
    && rm -rf /var/lib/apt/lists/*

# Configurar GD con soporte para JPEG, PNG, WebP, GIF, FreeType
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

# Instalar extensiones PDO MySQL y GD
RUN docker-php-ext-install pdo pdo_mysql gd

# Habilitar módulos necesarios de Apache
RUN a2enmod ssl rewrite

# Copiar configuración SSL
COPY 000-default-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf

# Habilitar sitio SSL
RUN a2ensite 000-default-ssl