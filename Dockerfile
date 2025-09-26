FROM php:8.2-apache

# Copiar archivos de la aplicación al directorio de Apache
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html/

# Exponer puerto
EXPOSE 80

CMD ["apache2-foreground"]