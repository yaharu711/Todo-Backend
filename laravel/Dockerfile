FROM php:8.2-apache

WORKDIR /var/www

#Render.comがコンテナをビルドする時に環境変数から渡してくれる変数を指定
ARG NEWRELIC_LICENSE_KEY
ARG NEWRELIC_APPLICATION_NAME

ENV TZ Asia/Tokyo
#一部の操作（特にグローバルパッケージのインストールなど）はスーパーユーザー権限が必要なため
ENV COMPOSER_ALLOW_SUPERUSER 1
#ARGで受け取ったNewRelic用の変数を環境変数に設定
ENV NEWRELIC_LICENSE_KEY=${NEWRELIC_LICENSE_KEY}
ENV NEWRELIC_APPLICATION_NAME=${NEWRELIC_APPLICATION_NAME}

RUN apt-get update && \
	apt-get install -y git unzip libzip-dev libpq-dev libicu-dev libonig-dev libjpeg62-turbo-dev libpng-dev && \
    docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd && \
	docker-php-ext-install intl pdo_pgsql zip bcmath

COPY ./prod/php.ini /usr/local/etc/php/php.ini
COPY ./prod/apache/httpd.conf /etc/apache2/sites-available/000-default.conf
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer
COPY ./src .
COPY ./prod/scripts/laravel-deploy.sh ./scripts/start.sh

#.htaccessで必要
RUN mv /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled && \
    mv /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/

RUN /bin/sh -c a2enmod rewrite

RUN chown www-data storage/ -R

RUN chmod +x ./scripts/start.sh
CMD ["./scripts/start.sh"]