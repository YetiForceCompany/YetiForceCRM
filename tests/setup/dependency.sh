#########################################
# Installation of the developer version:
#########################################

echo " -----  Install yarn for public_html directory -----"
yarn install --force --modules-folder "./public_html/libraries"
yarn list

echo " -----  Install yarn for public_html directory -----"
cd public_html/src
yarn install --force
yarn list
cd ../../

echo " -----  Install composer -----"
rm -rf composer.json
rm -rf composer.lock
mv composer_dev.json composer.json
mv composer_dev.lock composer.lock
composer install --no-interaction


#########################################
# Installation of the production version:
#########################################
#yarn install --force --modules-folder "./public_html/libraries" --production=true
#cd public_html/src
#yarn install --force --production=true
#cd ../../
#composer install --no-interaction --no-dev
