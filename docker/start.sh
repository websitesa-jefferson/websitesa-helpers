#!/bin/bash

mkdir -p /app/logs

# Redireciona TUDO (stdout e stderr) para o log
# exec > >(tee -a /app/logs/start.log) 2>&1

# Remova se não precisar debugar cada comando (polui o log)
# set -x

php-fpm -D

# Timezone
if [ -f /usr/share/zoneinfo/America/Sao_Paulo ]; then
    ln -fs /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime
    if command -v dpkg-reconfigure >/dev/null 2>&1; then
        dpkg-reconfigure -f noninteractive tzdata
    fi
fi

# Inicia o NGINX em primeiro plano para manter o contêiner ativo
nginx -g "daemon off;"
