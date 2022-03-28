<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */
chdir(__DIR__);
require_once __DIR__ . '/include/main/WebUI.php';
require_once __DIR__ . '/include/RequirementsValidation.php';

if ('OPTIONS' === $_SERVER['REQUEST_METHOD']) {
	\App\Controller\Headers::getInstance()->send();
	return;
}
if (!\App\Config::main('application_unique_key', false)) {
	header('location: install/Install.php');
} else {
	\App\Process::$startTime = microtime(true);
	\App\Process::$requestMode = 'WebUI';

	$webUI = new Vtiger_WebUI();
	$webUI->process(\App\Request::init());
}
