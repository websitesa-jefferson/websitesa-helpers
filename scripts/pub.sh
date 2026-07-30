#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # sem cor

# Define a versão (via parâmetro $1 ou solicita ao usuário)
VERSION=$1
if [ -z "$VERSION" ]; then
    echo -e "${YELLOW}Buscando a última tag remota no GitHub...${NC}"
    LATEST_TAG=$(git ls-remote --tags origin 2>/dev/null | awk -F'/' '{print $3}' | grep -v '\^{}' | sort -V | tail -n 1)
    if [ -n "$LATEST_TAG" ]; then
        echo -e "Última tag remota: ${GREEN}${LATEST_TAG}${NC}"
    else
        echo -e "Nenhuma tag remota encontrada."
    fi

    read -p "Digite a versão (ex: 1.0.0): " VERSION
fi

if [ -z "$VERSION" ]; then
    echo -e "\n${RED}Erro: A versão não pode ser vazia.${NC}\n"
    exit 1
fi

# Garante prefixo 'v' se não informado
if [[ "$VERSION" != v* ]]; then
    VERSION="v$VERSION"
fi

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
echo -e "\n${YELLOW}Sobreescrevendo tag local ($VERSION)...${NC}\n"
git tag -f "$VERSION"
if [ $? -ne 0 ]; then
    echo -e "\n${RED}Erro ao sobreescrever tag local. Verifique sua conexão e permissões.${NC}\n"
    exit 1
fi

echo -e "\n${YELLOW}Sobreescrevendo tag remota ($VERSION)...${NC}\n"
git push origin -f "$VERSION"
if [ $? -ne 0 ]; then
    echo -e "\n${RED}Erro ao sobreescrever tag remota. Verifique sua conexão e permissões.${NC}\n"
    exit 1
fi

echo -e "\n${GREEN}Tag $VERSION atualizada com sucesso!${NC}\n"

chown -R 1000:1000 ~/.ssh

exit 0