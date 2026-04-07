FROM httpd:2.4

# 1. Parchear vulnerabilidades y limpiar cache
RUN apt-get update && apt-get upgrade -y && apt-get clean

# 2. Habilitar módulos de Proxy y Rewrite
RUN sed -i '/LoadModule proxy_module/s/^#//g' /usr/local/apache2/conf/httpd.conf && \
    sed -i '/LoadModule proxy_fcgi_module/s/^#//g' /usr/local/apache2/conf/httpd.conf && \
    sed -i '/LoadModule rewrite_module/s/^#//g' /usr/local/apache2/conf/httpd.conf

# 3. Configurar el VirtualHost
# Importante: Cambiamos el DocumentRoot a /var/www/html para que coincida con el contenedor PHP
RUN echo '<VirtualHost *:80>' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    DocumentRoot "/var/www/html"' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    <Directory "/var/www/html">' >> /usr/local/apache2/conf/httpd.conf && \
    echo '        AllowOverride All' >> /usr/local/apache2/conf/httpd.conf && \
    echo '        Require all granted' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    </Directory>' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    # Redirección de archivos .php al servicio "php" definido en docker-compose' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://php:9000/var/www/html/$1' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    DirectoryIndex index.php index.html' >> /usr/local/apache2/conf/httpd.conf && \
    echo '</VirtualHost>' >> /usr/local/apache2/conf/httpd.conf

# 4. Asegurar permisos (opcional pero recomendado para TFG)
RUN chown -R www-data:www-data /usr/local/apache2/logs/