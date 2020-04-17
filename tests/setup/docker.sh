#!/bin/bash

/usr/sbin/nginx -g "daemon off;" &
/etc/init.d/php7.3-fpm start
/usr/bin/mysqld_safe
