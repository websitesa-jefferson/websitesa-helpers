FROM yiisoftware/yii2-php:8.3-apache

RUN mkdir -p /app

WORKDIR /app

COPY . .

# PHP extensions & packages
RUN set -ex \
   && apt-get update \
   && apt-get -y install \
      git \
      unzip \
      nano \
   && apt-get clean \
   && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Start
COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD [ "bash", "/start.sh" ]
