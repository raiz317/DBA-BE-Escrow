FROM php:8.2-fpm

# Install dependensi sistem
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip nginx

# Bersihkan cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP untuk MySQL
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Ambil Composer terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur folder kerja
WORKDIR /var/www
COPY . /var/www

# Install dependensi Laravel tanpa dev tools
RUN composer install --no-dev --no-scripts --optimize-autoloader

# Atur konfigurasi Nginx untuk Hugging Face (Port 7860)
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Berikan izin akses folder storage dan cache
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Hugging Face mewajibkan aplikasi berjalan di port 7860
EXPOSE 7860
CMD service nginx start && php-fpm
