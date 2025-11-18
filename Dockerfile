FROM php:8.2-apache

# Instalar extensión PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar módulos necesarios de Apache
RUN a2enmod ssl rewrite

# Copiar configuración SSL
COPY 000-default-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf

# Habilitar el sitio SSL
RUN a2ensite 000-default-ssl