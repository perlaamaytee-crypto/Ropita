FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql
COPY . /var/www/html/
# Límite de subida de fotos (5 MB) y permisos de la carpeta uploads/
RUN { echo 'upload_max_filesize=5M'; echo 'post_max_size=6M'; } > /usr/local/etc/php/conf.d/uploads.ini \
 && mkdir -p /var/www/html/uploads \
 && chown -R www-data:www-data /var/www/html/uploads \
 && sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
ENV PORT=10000
RUN sed -i 's/80/${PORT}/g' \
 /etc/apache2/sites-available/000-default.conf \
 /etc/apache2/ports.conf
