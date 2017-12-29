echo " -----  Install and setup apache -----"
phpenv config-add tests/setup/php.ini

sudo apt-get update
sudo apt-get install apache2 libapache2-mod-fastcgi

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
