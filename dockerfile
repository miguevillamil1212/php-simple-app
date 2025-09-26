# Usar imagen oficial de PHP con Apache
FROM php:8.1-apache

# Información del maintainer
LABEL maintainer="tu-nombre <tu-email@dominio.com>"

# Copiar archivos de la aplicación
COPY src/ /var/www/html/

# Exponer puerto 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Comando por defecto
CMD ["apache2-foreground"]