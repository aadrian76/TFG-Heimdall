FROM httpd:2.4

# Actualizamos el sistema operativo base para parchear vulnerabilidades de seguridad
RUN apt-get update && apt-get upgrade -y && apt-get clean

# En Debian, el archivo de configuración principal está en /usr/local/apache2/conf/httpd.conf
# Habilitamos módulos necesarios para Proxy PHP-FPM
RUN sed -i '/LoadModule proxy_module/s/^#//g' /usr/local/apache2/conf/httpd.conf && \
    sed -i '/LoadModule proxy_fcgi_module/s/^#//g' /usr/local/apache2/conf/httpd.conf && \
    sed -i '/LoadModule rewrite_module/s/^#//g' /usr/local/apache2/conf/httpd.conf

# Configuramos el VirtualHost para conectar con el contenedor PHP
# Usamos el puerto 9000 que es el estándar de PHP-FPM
RUN echo '<VirtualHost *:80>' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    DocumentRoot "/usr/local/apache2/htdocs"' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    <Directory "/usr/local/apache2/htdocs">' >> /usr/local/apache2/conf/httpd.conf && \
    echo '        AllowOverride All' >> /usr/local/apache2/conf/httpd.conf && \
    echo '        Require all granted' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    </Directory>' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    # Redirección de archivos .php al contenedor de PHP mediante FastCGI' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://php:9000/var/www/html/$1' >> /usr/local/apache2/conf/httpd.conf && \
    echo '    DirectoryIndex index.php index.html' >> /usr/local/apache2/conf/httpd.conf && \
    echo '</VirtualHost>' >> /usr/local/apache2/conf/httpd.conf