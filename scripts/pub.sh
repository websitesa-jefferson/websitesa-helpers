#!/bin/bash

chown -R root:root ~/.ssh

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # sem cor

# Solicita a mensagem de commit
read -p "Digite a mensagem do commit: " commit_message

# Adiciona todos os arquivos
echo -e "\n${YELLOW}Adicionando arquivos na stage...${NC}\n"
git add .
if [ $? -ne 0 ]; then
    echo -e "\n${RED}Erro ao adicionar arquivos na stage.${NC}\n"
    exit 1
fi

# Faz o commit
echo -e "\n${YELLOW}Fazendo commit...${NC}\n"
git commit -m "$commit_message"
if [ $? -ne 0 ]; then
    echo -e "\n${RED}Erro ao criar commit. Verifique se há alterações para commitar.${NC}\n"
    exit 1
fi

# Faz o push
echo -e "\n${YELLOW}Enviando para o repositório...${NC}\n"
git push -u origin main
if [ $? -ne 0 ]; then
    echo -e "\n${RED}Erro ao enviar para o repositório. Verifique sua conexão e permissões.${NC}\n"
    exit 1
fi

echo -e "\n${GREEN}Push realizado com sucesso!${NC}\n"

# git tag
echo -e "\n${YELLOW}Sobreescrevendo tag local...${NC}\n"
git tag -f v1.0.0
if [ $? -ne 0 ]; then
    echo -e "\n${RED}Erro ao sobreescrever tag local. Verifique sua conexão e permissões.${NC}\n"
    exit 1
fi

echo -e "\n${YELLOW}Sobreescrevendo tag remota...${NC}\n"
git push origin -f v1.0.0
if [ $? -ne 0 ]; then
    echo -e "\n${RED}Erro ao sobreescrever tag remota. Verifique sua conexão e permissões.${NC}\n"
    exit 1
fi

echo -e "\n${GREEN}Tags atualizadas com sucesso!${NC}\n"

chown -R 1000:1000 ~/.ssh

exit 0