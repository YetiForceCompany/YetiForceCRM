FROM php:8.0-apache

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

ENV APACHE_DOCUMENT_ROOT /var/www/html/public_html

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update && apt-get install --no-install-recommends -y \
	git \
	gnupg \
	libc-client-dev\
	libcurl4-openssl-dev \
	libkrb5-dev \
	libpng-dev \
	libxml2-dev \
	libzip-dev  \
	lsb-release \
	libldap-dev \
	zlib1g-dev \
	&& curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
	&& apt-get install -y nodejs --no-install-recommends \
	&& npm install --global yarn@1 \
	&& docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
	&& docker-php-ext-install -j8 imap pdo_mysql curl gd xml zip soap iconv intl bcmath sockets exif ldap opcache \
	&& apt-get clean

WORKDIR /opt
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php && php -r "unlink('composer-setup.php');" && mv composer.phar /usr/local/bin/composer
COPY --chown=www-data:www-data --chmod=644 ./ /var/www/html

WORKDIR /var/www/html

RUN --mount=type=cache,target=/root/.yarn YARN_CACHE_FOLDER=/root/.yarn yarn install --modules-folder "./public_html/libraries" --ignore-optional --production=true
WORKDIR /var/www/html/public_html/src
RUN --mount=type=cache,target=/root/.yarn YARN_CACHE_FOLDER=/root/.yarn yarn install --ignore-optional --production=true

WORKDIR /var/www/html

RUN --mount=type=cache,target=/root/.composer/cache composer --no-interaction install --no-dev

WORKDIR /var/www/html

RUN install -owww-data -gwww-data -m755 -d config/Modules
RUN find . -type d -exec chown www-data:www-data -- {} \+
RUN find . -type d -exec chmod 755 -- {} \+

COPY ./docker_config/php/php.ini "$PHP_INI_DIR/php.ini"

SHELL ["/bin/sh", "-c"]
EXPOSE 80
