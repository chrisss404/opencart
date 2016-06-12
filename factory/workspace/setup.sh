#!/bin/bash

find /var/www/html -mindepth 1 -delete
cp -r /tmp/workspace/shop/upload/. /var/www/html

apache2-foreground > /dev/null 2>&1 &

/wait_for_service.sh ${MYSQL_HOST} 3306
php /var/www/html/install/cli_install.php install --db_hostname ${MYSQL_HOST} \
                               --db_username ${MYSQL_USER} \
                               --db_password ${MYSQL_PASSWORD} \
                               --db_database ${MYSQL_DATABASE} \
                               --db_driver mysqli \
                               --username ${SHOP_ADMIN_USER} \
                               --password ${SHOP_ADMIN_PASSWORD} \
                               --email ${SHOP_ADMIN_EMAIL} \
                               --http_server http://${VIRTUAL_HOST}/

rm -rf $(find /var/www/html -name ".git" -or -name ".gitignore")
rm -rf /var/www/html/install

mv /var/www/html/.htaccess.txt /var/www/html/.htaccess
chown -R www-data:www-data /var/www/html
