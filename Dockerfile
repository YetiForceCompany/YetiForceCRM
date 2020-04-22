FROM debian:buster

MAINTAINER m.krzaczkowski@yetiforce.com

ARG DEBIAN_FRONTEND=noninteractive
ARG DB_ROOT_PASS=1r2VdePVnNxluabdGuqh

ENV PHP_VER php7.3
ENV DB_USER_NAME yetiforce
ENV DB_USER_PASS Q4WK2yRUpliyjMRivDJE
ENV DB_PORT 3306
ENV PROVIDER docker
#INSTALL_MODE = PROD , DEV
ENV INSTALL_MODE PROD

RUN apt-get update && apt-get install -y --no-install-recommends apt-utils curl openssl wget ca-certificates apt-transport-https lsb-release gnupg && apt-get -y autoclean

RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
RUN	curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update
RUN apt-get install -y --no-install-recommends mariadb-server mariadb-client nginx nginx-extras "${PHP_VER}"-fpm "${PHP_VER}"-mysql "${PHP_VER}"-curl "${PHP_VER}"-intl "${PHP_VER}"-gd "${PHP_VER}"-fpm "${PHP_VER}"-bcmath "${PHP_VER}"-soap "${PHP_VER}"-ldap "${PHP_VER}"-imap "${PHP_VER}"-xml "${PHP_VER}"-cli "${PHP_VER}"-zip "${PHP_VER}"-json "${PHP_VER}"-opcache "${PHP_VER}"-mbstring php-apcu php-imagick php-sodium zip unzip mc htop openssh-server git nodejs npm yarn cron && apt-get -y autoclean

# RUN apt-cache search php
RUN dpkg --get-selections | grep php

RUN rm /var/www/html/index.nginx-debian.html
COPY ./tests/setup/db/mysql.cnf /etc/mysql/mariadb.conf.d/50-server.cnf
COPY ./tests/setup/nginx/vhost.conf /etc/nginx/sites-available/default
COPY ./ /var/www/html
COPY ./tests/setup/crons.conf /etc/cron.d/yetiforcecrm
COPY ./tests/setup/php/prod.ini /etc/php/7.3/mods-available/yetiforce.ini
COPY ./tests/setup/docker_entrypoint.sh /
RUN rm /var/www/html/.user.ini
RUN rm /var/www/html/public_html/.user.ini

RUN	service mysql start; \
	mysql -uroot mysql; \
	mysqladmin password "$DB_ROOT_PASS"; \
	echo "UPDATE mysql.user SET Password=PASSWORD('$DB_ROOT_PASS') WHERE User='root';" | mysql --user=root;\
	echo "DELETE FROM mysql.user WHERE User='';" | mysql --user=root;\
	echo "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');" | mysql --user=root; \
	echo "DELETE FROM mysql.db WHERE Db='test' OR Db='test\_%';" | mysql --user=root; \
	echo "CREATE DATABASE yetiforce;" | mysql --user=root;\
	echo "CREATE USER 'yetiforce'@'localhost' IDENTIFIED BY '$DB_USER_PASS';" | mysql --user=root;\
	echo "GRANT ALL PRIVILEGES ON yetiforce.* TO 'yetiforce'@'localhost';" | mysql --user=root;\
	echo "FLUSH PRIVILEGES;" | mysql --user=root

RUN crontab /etc/cron.d/yetiforcecrm
RUN ln -s /etc/php/7.3/mods-available/yetiforce.ini /etc/php/7.3/cli/conf.d/30-yetiforce.ini
RUN ln -s /etc/php/7.3/mods-available/yetiforce.ini /etc/php/7.3/fpm/conf.d/30-yetiforce.ini
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
RUN	chmod +x /var/www/html/tests/setup/dependency.sh
RUN	chmod +x /docker_entrypoint.sh
RUN	/var/www/html/tests/setup/dependency.sh
RUN chown -R www-data:www-data /var/www/
RUN /usr/bin/perl -pi -e "s/password =/password = $DB_ROOT_PASS/g" /etc/mysql/debian.cnf

WORKDIR /var/www/html

EXPOSE 80
EXPOSE 3306

ENTRYPOINT [ "/docker_entrypoint.sh" ]
