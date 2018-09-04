sudo cp tests/setup/my.cnf /etc/mysql/conf.d/my.cnf
sudo service mysql restart
composer install
vendor/bin/phpunit --version
yarn install --force --modules-folder "./public_html/libraries"
sudo wget https://scrutinizer-ci.com/ocular.phar
sudo chmod +x ocular.phar
sudo mv ocular.phar /usr/local/bin/ocular
mysql -e "create database IF NOT EXISTS yetiforce;" -uroot
cp tests/setup/config.inc.txt config/config.inc.php
cp tests/setup/debug.txt config/debug.php
cp tests/setup/developer.txt config/developer.php
cp tests/setup/api.txt config/api.php
cp tests/setup/.htaccess .htaccess
# cp vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/phpunit_coverage.php phpunit_coverage.php
