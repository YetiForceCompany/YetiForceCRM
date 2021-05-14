#!/bin/bash
#########################################
# Installation dependency
#########################################
if [ "$GUI_MODE" == "true" ]; then
	echo " -----  yarn --version -----"
	yarn --version
	cd "$(dirname "$0")/../../" || { echo "Failure"; exit 1; }

	echo " -----  Install yarn for public_html directory (mode $INSTALL_MODE) -----"

	yarn install --modules-folder "./public_html/libraries" --production=true --ignore-optional

	echo " -----  Install yarn for public_html directory (mode $INSTALL_MODE) -----"

	cd public_html/src || { echo "Failure"; exit 1; }
	yarn install --production=true --ignore-optional

	cd ../../
fi

echo " -----  composer --version -----"
composer --version
echo " -----  Install composer (mode $INSTALL_MODE) -----"
if [ "$INSTALL_MODE" != "PROD" ]; then
	rm -rf composer.json
	rm -rf composer.lock
	mv composer_dev.json composer.json
	mv composer_dev.lock composer.lock
	echo " -----  composer install --no-interaction  --quiet -----"
	composer install --no-interaction --quiet
else
	echo " -----  composer install --no-interaction --no-dev -----"
	composer install --no-interaction --no-dev
fi
