/usr/local/bin/phpunit --version
composer install --no-dev
sudo wget https://scrutinizer-ci.com/ocular.phar
sudo chmod +x ocular.phar
sudo mv ocular.phar /usr/local/bin/ocular
mysql -e "SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION'"
mysql -e "create database IF NOT EXISTS yetiforce;" -uroot
cp tests/config.inc.txt config/config.inc.php
cp tests/debug.txt config/debug.php
cp tests/developer.txt config/developer.php
cp tests/.htaccess .htaccess
