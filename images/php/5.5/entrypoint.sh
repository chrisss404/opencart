#!/bin/bash

if [ -n "${DOWNLOAD_PLUGIN}" ]; then
    if curl -sIL ${DOWNLOAD_PLUGIN} | grep -q "HTTP/1.1 200 OK"; then
        curl -sL ${DOWNLOAD_PLUGIN} | tar -xzv --strip 1
    fi
fi

/wait_for_service.sh ${MYSQL_HOST} 3306
php /var/www/update_shop_configuration.php

exec apache2-foreground
