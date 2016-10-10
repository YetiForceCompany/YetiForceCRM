<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

$startTime = microtime(true);

define('REQUEST_MODE', 'WebUI');
define('ROOT_DIRECTORY', __DIR__);

require 'include/RequirementsValidation.php';
require 'include/Webservices/Relation.php';
require 'include/main/WebUI.php';

if(AppConfig::debug('DISPLAY_DEBUG_CONSOLE')){
	\includes\Debuger::init();
}

$webUI = new Vtiger_WebUI();
$webUI->process(AppRequest::init());

