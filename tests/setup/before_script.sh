/usr/local/bin/phpunit --version
composer install
sudo wget https://scrutinizer-ci.com/ocular.phar
sudo chmod +x ocular.phar
sudo mv ocular.phar /usr/local/bin/ocular
mysql -e "SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION'"
mysql -e "create database IF NOT EXISTS yetiforce;" -uroot
cp tests/setup/config.inc.txt config/config.inc.php
cp tests/setup/debug.txt config/debug.php
cp tests/setup/developer.txt config/developer.php
cp tests/setup/api.txt config/api.php
cp tests/setup/.htaccess .htaccess
cp vendor/phpunit/phpunit-selenium/PHPUnit/Extensions/SeleniumCommon/phpunit_coverage.php phpunit_coverage.php
