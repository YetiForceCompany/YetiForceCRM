#!/usr/bin/env bash
# https://developers.supportbee.com/blog/setting-up-cucumber-to-run-with-Chrome-on-Linux/
# https://gist.github.com/curtismcmullan/7be1a8c1c841a9d8db2c
# https://stackoverflow.com/questions/10792403/how-do-i-get-chrome-working-with-selenium-using-php-webdriver
# https://stackoverflow.com/questions/26133486/how-to-specify-binary-path-for-remote-chromedriver-in-codeception
# https://stackoverflow.com/questions/40262682/how-to-run-selenium-3-x-with-chrome-driver-through-terminal
# https://askubuntu.com/questions/760085/how-do-you-install-google-chrome-on-ubuntu-16-04

#https://github.com/vvo/selenium-standalone#install--run
#https://gist.github.com/ziadoz/3e8ab7e944d02fe872c3454d17af31a5
#https://github.com/php-webdriver/php-webdriver/blob/0510696880e8f88026d9266344e3af7ee79040dd/.github/workflows/tests.yaml

serverUrl='http://127.0.0.1:4444'

# Versions
#CHROME_DRIVER_VERSION=`curl -sS https://chromedriver.storage.googleapis.com/LATEST_RELEASE`
SELENIUM_STANDALONE_VERSION=3.6.0
#SELENIUM_SUBDIR=$(echo "$SELENIUM_STANDALONE_VERSION" | cut -d"." -f-2)

# Install sources.list.d.
echo '-- # Install sources.list.d. --'
echo "deb http://security.debian.org/debian-security stretch/updates main" >> /etc/apt/sources.list.d/debian-security.list
curl -sS -o - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add
echo "deb https://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google-chrome.list
apt-get -y update

# Install dependencies.
echo '-- # Install dependencies. --'
#apt-get -y install --no-install-recommends openjdk-8-jre-headless xvfb xauth libxi6 libgconf-2-4
apt-get -y install --no-install-recommends xvfb openjdk-8-jre-headless

# Install Chrome.
echo '-- # Install Chrome. --'
apt-get -y install --no-install-recommends google-chrome-stable

# Install ChromeDriver.
#echo '-- Install ChromeDriver. --'
#wget -q -N https://chromedriver.storage.googleapis.com/$CHROME_DRIVER_VERSION/chromedriver_linux64.zip -P ~/
#unzip ~/chromedriver_linux64.zip -d ~/
#rm ~/chromedriver_linux64.zip
#mv -f ~/chromedriver /usr/local/bin/chromedriver
#chown root:root /usr/local/bin/chromedriver
#chmod 0755 /usr/local/bin/chromedriver


# Install Selenium.
#echo '-- # Install Selenium. --'
#wget -q -N https://selenium-release.storage.googleapis.com/$SELENIUM_SUBDIR/selenium-server-standalone-$SELENIUM_STANDALONE_VERSION.jar -P ~/
#mv -f ~/selenium-server-standalone-$SELENIUM_STANDALONE_VERSION.jar /usr/local/bin/selenium-server-standalone.jar
#chown root:root /usr/local/bin/selenium-server-standalone.jar
#chmod 0755 /usr/local/bin/selenium-server-standalone.jar

# Run Chrome via Selenium Server
#echo '-- # Run Chrome via Selenium Server --'
#xvfb-run java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -jar /usr/local/bin/selenium-server-standalone.jar > /var/www/html/cache/logs/selenium.log 2>&1 &
#debug
#xvfb-run java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -jar /usr/local/bin/selenium-server-standalone.jar -debug
#echo '-- # chromedriver --url-base=/wd/hub --'


#xvfb-run java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -jar $HOME/selenium-server-standalone.jar -debug > /tmp/selenium.log &

echo '--  # npm install selenium-standalone -g  --'
npm install selenium-standalone -g > /var/www/html/cache/logs/selenium1_npm_install.log 2>&1 &
echo '--  # selenium-standalone install  --'
selenium-standalone install --singleDriverInstall=chrome --version=$SELENIUM_STANDALONE_VERSION > /var/www/html/cache/logs/selenium2_install_install.log 2>&1 &
echo '--  # selenium-standalone start  --'
selenium-standalone start --singleDriverInstall=chrome --version=$SELENIUM_STANDALONE_VERSION > /var/www/html/cache/logs/selenium3_start.log 2>&1 &

selenium-standalone start  --version=3.6.0

echo "-- # $serverUrl/wd/hub/status --"
wget --retry-connrefused --tries=1 --waitretry=1 --output-file=/dev/null http://127.0.0.1:4444/wd/hub/status -O /dev/null
if [ ! $? -eq 0 ]; then
    echo " Selenium Server not started !!!"
else
    echo " Selenium Server started !!!"
fi

