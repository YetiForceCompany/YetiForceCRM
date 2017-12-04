serverUrl='http://127.0.0.1:4444'

CHROME_DRIVER_VERSION=`curl -sS chromedriver.storage.googleapis.com/LATEST_RELEASE`
SELENIUM_STANDALONE_VERSION=3.6.0
SELENIUM_SUBDIR=$(echo "$SELENIUM_STANDALONE_VERSION" | cut -d"." -f-2)

sudo apt-get update
sudo apt-get install -y unzip openjdk-8-jre-headless xvfb libxi6 libgconf-2-4

wget -N https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb -P ~/
sudo dpkg -i --force-depends ~/google-chrome-stable_current_amd64.deb
sudo apt-get -f install -y
sudo dpkg -i --force-depends ~/google-chrome-stable_current_amd64.deb

wget -N http://chromedriver.storage.googleapis.com/$CHROME_DRIVER_VERSION/chromedriver_linux64.zip -P ~/
unzip ~/chromedriver_linux64.zip -d ~/
rm ~/chromedriver_linux64.zip
sudo mv -f ~/chromedriver /usr/local/bin/chromedriver
sudo chown root:root /usr/local/bin/chromedriver
sudo chmod 0755 /usr/local/bin/chromedriver

wget -N http://selenium-release.storage.googleapis.com/$SELENIUM_SUBDIR/selenium-server-standalone-$SELENIUM_STANDALONE_VERSION.jar -P ~/
sudo mv -f ~/selenium-server-standalone-$SELENIUM_STANDALONE_VERSION.jar /usr/local/bin/selenium-server-standalone.jar
sudo chmod 0755 /usr/local/bin/selenium-server-standalone.jar

xvfb-run java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -jar /usr/local/bin/selenium-server-standalone.jar -debug > /tmp/selenium.log &
wget --retry-connrefused --tries=60 --waitretry=1 --output-file=/dev/null $serverUrl/wd/hub/status -O /dev/null
if [ ! $? -eq 0 ]; then
    echo "Selenium Server not started"
else
    echo "Finished setup"
fi
