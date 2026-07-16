ARG PHP_BASE_IMAGE_VERSION=8.3-fpm

# ==========================================
# Estágio 1: Builder (Gera dependências)
# ==========================================
FROM yiisoftware/yii2-php:${PHP_BASE_IMAGE_VERSION} AS builder

WORKDIR /app

# Copia arquivos do Composer e pacotes locais
COPY composer.json composer.lock ./

# Se houver pacotes privados que exigem chaves SSH no build,
# use: RUN --mount=type=ssh composer install ...
# Primeiro instala dependências sem rodar scripts nem gerar autoloader para cachear a camada
RUN composer install --no-scripts --no-autoloader --no-interaction --prefer-dist

# Copia o código da aplicação
COPY . .

# Agora gera o autoloader otimizado e executa os scripts de pós-instalação
RUN composer dump-autoload --optimize --no-interaction

# ==========================================
# Estágio 2: Runner (Imagem final leve/rápida)
# ==========================================
FROM php:${PHP_BASE_IMAGE_VERSION}-alpine

ENV TZ=America/Sao_Paulo

WORKDIR /app

# Instala extensões PHP essenciais usando o instalador de extensões oficial da comunidade (sem imagick)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    redis \
    zip \
    bcmath \
    gmp \
    opcache

# Instala pacotes do sistema necessários usando o apk do Alpine
RUN apk add --no-cache \
    curl \
    cronie \
    mariadb-client \
    nginx \
    bash \
    tzdata \
    && cp /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo ${TZ} > /etc/timezone

# Configura diretórios do Nginx para manter compatibilidade com a estrutura do Debian
RUN mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled /run/nginx \
   && chown -R www-data:www-data /var/lib/nginx /var/log/nginx

# Nginx
COPY docker/configs/nginx.conf /etc/nginx/nginx.conf
COPY docker/configs/nginx-site.conf /etc/nginx/sites-available/default
RUN rm -f /etc/nginx/sites-enabled/default \
   && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Xdebug
COPY docker/configs/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# PHP Config
COPY docker/configs/php.ini /usr/local/etc/php/php.ini
COPY docker/configs/php-fpm.conf /usr/local/etc/php-fpm.d/zz-optimized.conf
COPY docker/configs/zz-performance.ini /usr/local/etc/php/conf.d/zz-performance.ini

# Start
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Copia a aplicação inteira pronta (código + vendor) do builder
COPY --from=builder /app /app

EXPOSE 80

CMD [ "bash", "/start.sh" ]
