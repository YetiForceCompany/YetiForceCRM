#*********************************************************************************
# The contents of this file are subject to the vtiger CRM Public License Version 1.0
# ("License"); You may not use this file except in compliance with the License
# The Original Code is:  vtiger CRM Open Source
# The Initial Developer of the Original Code is vtiger.
# Portions created by vtiger are Copyright (C) vtiger.
# All Rights Reserved.
#
# ********************************************************************************

export CRM_ROOT_DIR=`dirname "$0"`/..

export USE_PHP=php
#export USE_PHP=/usr/local/php73/bin/php73
#export USE_PHP=/usr/local/php74/bin/php74

cd $CRM_ROOT_DIR

# TO RUN ALL CORN JOBS
$USE_PHP -f cron.php
