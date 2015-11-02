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
include_once 'include/Webservices/Relation.php';
include_once 'include/main/WebUI.php';
require_once('vtlib/Vtiger/Cron.php');
require_once('modules/Emails/mail.php');

Vtiger_Session::init();
$authenticatedUserId = Vtiger_Session::get('authenticated_user_id');
$appUniqueKey = Vtiger_Session::get('app_unique_key');
if (PHP_SAPI === 'cli' || PHP_SAPI === 'cgi-fcgi' || (!empty($authenticatedUserId) && !empty($appUniqueKey) && $appUniqueKey == vglobal('application_unique_key'))) {
	$log = LoggerManager::getLogger('CRON');
	vglobal('log', $log);

	$cronTasks = false;
	if (isset($_REQUEST['service'])) {
		// Run specific service
		$cronTasks = [Vtiger_Cron::getInstance($_REQUEST['service'])];
	} else {
		// Run all service
		$cronTasks = Vtiger_Cron::listAllActiveInstances();
	}

	$cronStarts = date('Y-m-d H:i:s');

	//set global current user permissions
	$current_user = vglobal('current_user');
	$current_user = Users::getActiveAdminUser();

	echo sprintf('---------------  %s | Start CRON  ----------', date('Y-m-d H:i:s')) . PHP_EOL;
	foreach ($cronTasks as $cronTask) {
		try {
			// Timeout could happen if intermediate cron-tasks fails
			// and affect the next task. Which need to be handled in this cycle.				
			if ($cronTask->hadTimeout()) {
				echo sprintf('%s | %s - Cron task had timedout as it was not completed last time it run' . PHP_EOL, date('Y-m-d H:i:s'), $cronTask->getName());
				if (vglobal('unblockedTimeoutCronTasks')) {
					$cronTask->unlockTask();
				}
			}

			// Not ready to run yet?
			if ($cronTask->isRunning()) {
				$log->fatal($cronTask->getName() . ' - Task omitted, it has not been finished during the last scanning');
				echo sprintf('%s | %s - Task omitted, it has not been finished during the last scanning' . PHP_EOL, date('Y-m-d H:i:s'), $cronTask->getName());
				continue;
			}

			// Not ready to run yet?
			if (!$cronTask->isRunnable()) {
				$log->info($cronTask->getName() . ' - Not ready to run as the time to run again is not completed');
				echo sprintf('%s | %s - Not ready to run as the time to run again is not completed' . PHP_EOL, date('Y-m-d H:i:s'), $cronTask->getName());
				continue;
			}

			// Mark the status - running		
			$cronTask->markRunning();
			echo sprintf('%s | %s - Start task' . PHP_EOL, date('Y-m-d H:i:s'), $cronTask->getName());

			checkFileAccess($cronTask->getHandlerFile());
			ob_start();
			require_once $cronTask->getHandlerFile();
			$taskResponse = ob_get_contents();
			ob_end_clean();
			if ($taskResponse != '') {
				$log->warn($cronTask->getName() . ' - The task returned a message:' . PHP_EOL . $taskResponse);
				echo 'Task response:' . PHP_EOL . $taskResponse . PHP_EOL;
			}

			// Mark the status - finished
			$cronTask->markFinished();
			echo sprintf('%s | %s - End task', date('Y-m-d H:i:s'), $cronTask->getName()) . PHP_EOL;
		} catch (AppException $e) {
			echo sprintf('%s | ERROR: %s - Cron task execution throwed exception.' . PHP_EOL, date('Y-m-d H:i:s'), $cronTask->getName());
			echo $e->getMessage() . PHP_EOL;
		}
	}
	echo sprintf('===============  %s | End CRON  ==========', date('Y-m-d H:i:s')) . PHP_EOL;
} else {
	echo("Access denied!");
}
