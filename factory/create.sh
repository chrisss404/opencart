#!/bin/bash

if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi

SHOP_VERSION="2.2.0.0"

build1=("5.5" "5.5")
build2=("5.6" "5.6")
build3=("7.0" "5.7")

rm -rf workspace/shop
git clone --depth 1 -b ${SHOP_VERSION} https://github.com/opencart/opencart.git workspace/shop

rm -rf volume-php volume-mysql
docker-compose rm -f --all

for i in $(seq 3); do
    php_version=$(eval echo \${build${i}[0]})
    mysql_version=$(eval echo \${build${i}[1]})

    # delete previously created artifacts by copying empty ones
    cp ../images/php/files.tar.gz ../images/php/${php_version}
    cp ../images/mysql/data.tar.gz ../images/mysql/${mysql_version}

    # install shop
    docker-compose build php${php_version/./-} mysql${mysql_version/./-}
    docker-compose run --service-ports php${php_version/./-}
    docker-compose stop mysql${mysql_version/./-}

    # cleanup mysql files
    rm -rf volume-mysql/ib_*

    # create and move artifacts
    tar cfvz files.tar.gz -C volume-php .
    tar cfvz data.tar.gz -C volume-mysql .
    chown $(stat -c '%U:%G' .) *.tar.gz
    mv files.tar.gz ../images/php/${php_version}
    mv data.tar.gz ../images/mysql/${mysql_version}

    # delete volumes
    rm -rf volume-php volume-mysql
done

docker-compose rm -f --all
rm -rf workspace/shop
