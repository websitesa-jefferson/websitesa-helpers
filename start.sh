#!/bin/bash

set -e

git config --global --add safe.directory /app
git config --global user.name "Jefferson Costa Dias"
git config --global user.email "jeffersoncosta2@gmail.com"

# Alterar para "dependencia":"*" as que quebrarem, depois trava na versão
if [ ! -d "/vendor" ]; then
    composer install
else
    composer update --prefer-dist --no-interaction
fi

/usr/sbin/apachectl -D FOREGROUND
