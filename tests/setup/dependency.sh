#!/bin/bash
#########################################
# Installation dependency
#########################################
if [ "$GUI_MODE" == "true" ]; then
	yarn --version
	cd "$(dirname "$0")/../../"

	echo " -----  Install yarn for public_html directory (mode $INSTALL_MODE) -----"

	if [ "$INSTALL_MODE" != "PROD" ]; then
		yarn install --modules-folder "./public_html/libraries"
		yarn list
	else
		yarn install --modules-folder "./public_html/libraries" --production=true --ignore-optional
	fi

	echo " -----  Install yarn for public_html directory (mode $INSTALL_MODE) -----"

	cd public_html/src
	if [ "$INSTALL_MODE" != "PROD" ]; then
		yarn install
		yarn list
	else
	yarn install --production=true --ignore-optional
	fi
	cd ../../
fi

echo " -----  Install composer (mode $INSTALL_MODE) -----"
composer -V
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
