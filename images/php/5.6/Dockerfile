FROM php:5.6-apache

ENV MYSQL_HOST db
ENV VIRTUAL_HOST 127.0.0.1

# Install netcat, required in wait for service script
RUN apt-get update && apt-get install -y \
        netcat \
    && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite for apache
RUN a2enmod rewrite

# Install mcrypt php extension
RUN apt-get update && apt-get install -y \
        libmcrypt-dev \
    && docker-php-ext-install -j$(nproc) mcrypt \
    && rm -rf /var/lib/apt/lists/*

# Install gd php extension
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng12-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && rm -rf /var/lib/apt/lists/*

# Install mysqli php extension
RUN apt-get update && apt-get install -y \
        libmysqlclient-dev \
    && docker-php-ext-install -j$(nproc) mysqli \
    && rm -rf /var/lib/apt/lists/*

# Install zip php extension
RUN apt-get update && apt-get install -y \
        zlib1g-dev \
    && docker-php-ext-install -j$(nproc) zip \
    && rm -rf /var/lib/apt/lists/*

# Add setup/start scripts
COPY wait_for_service.sh /wait_for_service.sh
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /*.sh

# Add application contents
ADD files.tar.gz /var/www/html
COPY update_shop_configuration.php /var/www/update_shop_configuration.php

CMD ["/entrypoint.sh"]
