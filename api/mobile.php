<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */
$currentPath = dirname(__FILE__);
$crmPath = $currentPath . '/../';
chdir($crmPath);

require_once('include/database/PearDatabase.php');
require_once 'libraries/restler/restler.php';
require_once('include/utils/VtlibUtils.php');
AppConfig::iniSet('error_log', ROOT_DIRECTORY . '/cache/logs/mobileApps.log');

if (!in_array('mobile', $enabledServices)) {
	$apiLog = new \Exception\NoPermittedToApi();
	$apiLog->stop(['status' => 0, 'message' => 'Mobile - Service is not active']);
}

$adb = PearDatabase::getInstance();
$log = &LoggerManager::getLogger('mobileApps');
vglobal('log', $log);

$adb = PearDatabase::getInstance();
$log->info('Start mobile service');

spl_autoload_register('spl_autoload');
$r = new Restler();
$r->addAPIClass('Test');
$r->addAPIClass('HistoryCall');
$r->addAPIClass('PushCall');
$r->handle();
$log->info('End mobile service');
