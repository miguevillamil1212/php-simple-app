FROM php:8.2-cli
COPY . /var/www/html/
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html/"]
EXPOSE 80