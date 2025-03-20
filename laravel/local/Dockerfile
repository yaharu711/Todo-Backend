FROM php:8.2-apache

ENV TZ Asia/Tokyo

WORKDIR /var/www

RUN apt-get update && \
	apt-get install -y git unzip libzip-dev libpq-dev libicu-dev libonig-dev libjpeg62-turbo-dev libpng-dev nodejs npm && \
    docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd &&\
	docker-php-ext-install intl pdo_pgsql zip bcmath

COPY ./php.ini /usr/local/etc/php/php.ini
COPY  ./apache/local.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

#ログファイル生成
RUN mkdir /usr/local/etc/php/log && \
  touch /usr/local/etc/php/log/php_errors.log && \
  chmod 777 /usr/local/etc/php/log/php_errors.log

#.htaccessで必要
RUN mv /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled && \
    mv /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/
RUN /bin/sh -c a2enmod rewrite

# service accountのファイルをコンテナ内に保存する
# Render.comもetc/secrets/<filename>になるので、ローカルでも揃える
COPY ./secrets /etc/secrets/
# 実行権限もないとディレクトリにアクセスできないため所有者以外にも5を付与している
RUN chmod 755 /etc/secrets/
