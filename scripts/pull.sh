#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # sem cor

# Faz o push
echo -e "\n${YELLOW}Puxando para o repositório local...${NC}\n"
git pull origin main
if [ $? -ne 0 ]; then
    echo -e "\n${RED}Erro ao puxar para o repositório local. Verifique sua conexão e permissões.${NC}\n"
    exit 1
fi

echo -e "\n${GREEN}Pull realizado com sucesso!${NC}\n"

chown -R 1000:1000 ~/.ssh

exit 0