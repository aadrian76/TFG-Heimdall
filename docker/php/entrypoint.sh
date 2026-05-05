#!/bin/sh
# Crea directorios si no existen y da permisos
mkdir -p /var/data
mkdir -p /var/www/html/fotos
chmod 777 /var/data
chmod 777 /var/www/html/fotos

# Arranca PHP-FPM
exec php-fpm