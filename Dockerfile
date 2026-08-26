FROM uspdev/uspdev-php-apache:8.4

# PostgreSQL
USER root
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo_pgsql && \
    rm -rf /var/lib/apt/lists/*

# apache
RUN sed -i 's|/var/www/html|/var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Altera a porta do Apache de 80 para 3142 para usar com outros sistemas no docker-compose.yml
RUN sed -i 's/Listen 80/Listen 3142/g' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:3142>/g' /etc/apache2/sites-available/000-default.conf
EXPOSE 3142

# composer
USER www-data
COPY --chown=www-data . .
RUN composer install

CMD ["apache2-foreground"]
