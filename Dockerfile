ARG PHP_BASE_IMAGE_VERSION
FROM yiisoftware/yii2-php:${PHP_BASE_IMAGE_VERSION}

ENV DEBIAN_FRONTEND=noninteractive

RUN mkdir -p /app

WORKDIR /app

# PHP extensions & packages
RUN set -ex \
   && apt-get update \
   && apt-get -y install \
      git \
      unzip \
      nano \
      openssh-client \
   && apt-get clean \
   && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Start
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Código da aplicação (por último para cache)
COPY . .

CMD [ "bash", "/start.sh" ]
