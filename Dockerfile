FROM php:8.2-fpm

# Install OS dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libzip-dev 

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Boost PHP limits for heavy Word / Content parsing
RUN echo 'upload_max_filesize = 100M' > /usr/local/etc/php/conf.d/custom-limits.ini \
    && echo 'post_max_size = 100M' >> /usr/local/etc/php/conf.d/custom-limits.ini \
    && echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/custom-limits.ini \
    && echo 'max_execution_time = 300' >> /usr/local/etc/php/conf.d/custom-limits.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# We don't copy the whole codebase in development since we mount it in docker-compose, 
# but it's good practice to ensure the user exists
RUN chown -R www-data:www-data /var/www
