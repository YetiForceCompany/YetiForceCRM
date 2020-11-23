echo " -----  Install and setup apache -----"
phpenv config-add tests/setup/php/dev.ini

echo " -----  configuration php-fpm  -----"
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf
sudo a2enmod rewrite actions fastcgi alias
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

echo " -----  change permissions  -----"
sudo sed -i -e "s,www-data,travis,g" /etc/apache2/envvars
sudo chown -R travis:travis /var/lib/apache2/fastcgi

echo " -----  enable php-fpm  -----"
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

echo " -----  configure apache virtual hosts  -----"
sudo cp -f tests/setup/travis-ci-apache /etc/apache2/sites-available/000-default.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/000-default.conf

echo " -----  restart apache2  -----"
sudo service apache2 restart

#if [[ ${TRAVIS_PHP_VERSION:0:3} == "7.0" ]]; then sudo cp Tests/build/www.conf ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/; fi

echo " -----  php info  -----"
echo " PHP_INT_MAX: "
php -r 'print(PHP_INT_MAX);'

echo " opcache: "
php -i | grep opcache.

echo " memory_limit: "
php -i | grep memory_limit
echo " free: "
free -m

echo " -----  /usr/lib/cgi-bin/  -----"

sudo ls -all /usr/lib/cgi-bin/

echo " -----  /var/lib/apache2/  -----"

sudo ls -all /var/lib/apache2/

echo " -----  /var/lib/apache2/module  -----"

sudo ls -all /var/lib/apache2/module

echo " -----  /etc/apache2/  -----"

sudo ls -all /etc/apache2/

echo " -----  /etc/apache2/apache2.conf  -----"

cat /etc/apache2/apache2.conf

echo " -----  -----"

echo " all service: "
service --status-all

echo " Apache enabled modules: "

apache2ctl -M

echo " -----  /etc/apache2/sites-available/000-default.conf  -----"

cat /etc/apache2/sites-available/000-default.conf

echo " -----  -----"
