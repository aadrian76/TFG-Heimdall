FROM php:8.3-fpm

# Instalamos dependencias del sistema para GD (imágenes) y utilidades
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Configuramos e instalamos PDO para MySQL y GD para imágenes
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql

# Damos permisos al usuario del servidor web
RUN chown -R www-data:www-data /var/www/html