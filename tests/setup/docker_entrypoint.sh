#!/bin/bash

service cron start
/usr/sbin/nginx -g "daemon off;" &
/etc/init.d/php$PHP_VER-fpm start
/usr/bin/mysqld_safe
