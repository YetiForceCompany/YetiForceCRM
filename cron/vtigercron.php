<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ****************************************************************************** */
chdir(dirname(__FILE__) . '/../');
/**
 * Start the cron services configured.
 */
require_once 'config/config.php';
require_once 'config/debug.php';
require_once 'config/performance.php';
include_once 'vtlib/Vtiger/Cron.php';
require_once('modules/Emails/mail.php');
// Extended inclusions
require_once 'include/Loader.php';
vimport('include.runtime.EntryPoint');

if (PHP_SAPI === "cli" || PHP_SAPI === "cgi-fcgi" || (isset($_SESSION["authenticated_user_id"]) && isset($_SESSION["app_unique_key"]) && $_SESSION["app_unique_key"] == $application_unique_key)) {
	$log = LoggerManager::getLogger('CRON');
	vglobal('log', $log);
	
	$cronTasks = false;
	if (isset($_REQUEST['service'])) {
		// Run specific service
		$cronTasks = array(Vtiger_Cron::getInstance($_REQUEST['service']));
	} else {
		// Run all service
		$cronTasks = Vtiger_Cron::listAllActiveInstances();
	}

	$cronStarts = date('Y-m-d H:i:s');

	//set global current user permissions
	$current_user = vglobal('current_user');
	$current_user = Users::getActiveAdminUser();

	echo sprintf('%s | Start CRON', date('Y-m-d H:i:s')) . "\n";
	foreach ($cronTasks as $cronTask) {
		try {
			$cronTask->setBulkMode(true);

			// Not ready to run yet?
			if (!$cronTask->isRunnable()) {
				echo sprintf("%s | %s - not ready to run as the time to run again is not completed\n", date('Y-m-d H:i:s'), $cronTask->getName());
				continue;
			}

			// Timeout could happen if intermediate cron-tasks fails
			// and affect the next task. Which need to be handled in this cycle.				
			if ($cronTask->hadTimedout()) {
				echo sprintf("%s | %s - cron task had timedout as it is not completed last time it run- restarting\n", date('Y-m-d H:i:s'), $cronTask->getName());
			}

			// Mark the status - running		
			$cronTask->markRunning();
			echo sprintf('%s | %s (%s) - Start task', date('Y-m-d H:i:s'), $cronTask->getName(), date('Y-m-d H:i:s', $cronTask->getLastStart())) . "\n";

			checkFileAccess($cronTask->getHandlerFile());
			require_once $cronTask->getHandlerFile();

			// Mark the status - finished
			$cronTask->markFinished();
			echo "\n" . sprintf('%s | %s (%s) - End task', date('Y-m-d H:i:s'), $cronTask->getName(), date('Y-m-d H:i:s', $cronTask->getLastStart()), date('Y-m-d H:i:s', $cronTask->getLastEnd())) . "\n";
		} catch (Exception $e) {
			echo sprintf("%s | ERROR: %s - cron task execution throwed exception.\n", date('Y-m-d H:i:s'), $cronTask->getName());
			echo $e->getMessage();
			echo "\n";
		}
	}
	echo sprintf('%s | End CRON', date('Y-m-d H:i:s')) . "\n";
} else {
	echo("Access denied!");
}
