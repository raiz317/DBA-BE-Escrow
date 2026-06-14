FROM php:8.3-fpm-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache nginx supervisor mariadb-client shadow bash
RUN docker-php-ext-install pdo_mysql

# Setup document root
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Copy nginx and supervisor configs
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Adjust permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]
