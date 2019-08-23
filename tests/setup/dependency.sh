#########################################
# Installation of the developer version:
#########################################

echo " -----  Install composer -----"
composer install --no-interaction

echo " -----  Install yarn for public_html directory -----"
yarn install --force --modules-folder "./public_html/libraries"

echo " -----  Install yarn for public_html directory -----"
cd public_html/src
yarn install --force
cd ../../

#########################################
# Installing the production version:
#########################################
#composer install --no-interaction --no-dev
#yarn install --force --modules-folder "./public_html/libraries" --production=true
#cd public_html/src
#yarn install --force --production=true
#cd ../../
