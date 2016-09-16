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
require_once 'modules/Emails/mail.php';

Vtiger_Session::init();
$authenticatedUserId = Vtiger_Session::get('authenticated_user_id');
$appUniqueKey = Vtiger_Session::get('app_unique_key');
$user = (!empty($authenticatedUserId) && !empty($appUniqueKey) && $appUniqueKey == AppConfig::main('application_unique_key'));
if (PHP_SAPI === 'cli' || PHP_SAPI === 'cgi-fcgi' || PHP_SAPI === 'ucgi5' || $user) {
	$log = LoggerManager::getLogger('CRON');
	vglobal('log', $log);

	$cronTasks = false;
	vtlib\Cron::setCronAction(true);
	if (AppRequest::has('service')) {
		// Run specific service
		$cronTasks = [vtlib\Cron::getInstance(AppRequest::get('service'))];
	} else {
		// Run all service
		$cronTasks = vtlib\Cron::listAllActiveInstances();
	}

	$cronStarts = date('Y-m-d H:i:s');

	//set global current user permissions
	$current_user = vglobal('current_user');
	$current_user = Users::getActiveAdminUser();

	if ($user) {
		echo '<pre>';
	}
	echo sprintf('---------------  %s | Start CRON  ----------', date('Y-m-d H:i:s')) . PHP_EOL;
	foreach ($cronTasks as $cronTask) {
		try {
			$log->info($cronTask->getName() . ' - Start');
			// Timeout could happen if intermediate cron-tasks fails
			// and affect the next task. Which need to be handled in this cycle.				
			if ($cronTask->hadTimeout()) {
				echo sprintf('%s | %s - Cron task had timedout as it was not completed last time it run' . PHP_EOL, date('Y-m-d H:i:s'), $cronTask->getName());
				if (AppConfig::main('unblockedTimeoutCronTasks')) {
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
			$startTime = microtime(true);

			vtlib\Deprecated::checkFileAccess($cronTask->getHandlerFile());
			ob_start();
			require_once $cronTask->getHandlerFile();
			$taskResponse = ob_get_contents();
			ob_end_clean();

			$taskTime = round(microtime(true) - $startTime, 2);
			if ($taskResponse != '') {
				$log->warn($cronTask->getName() . ' - The task returned a message:' . PHP_EOL . $taskResponse);
				echo 'Task response:' . PHP_EOL . $taskResponse . PHP_EOL;
			}

			// Mark the status - finished
			$cronTask->markFinished();
			echo sprintf('%s | %s - End task (%s s)', date('Y-m-d H:i:s'), $cronTask->getName(), $taskTime) . PHP_EOL;
			$log->info($cronTask->getName() . ' - End');
		} catch (\Exception\AppException $e) {
			echo sprintf('%s | ERROR: %s - Cron task execution throwed exception.', date('Y-m-d H:i:s'), $cronTask->getName()) . PHP_EOL;
			echo $e->getMessage() . PHP_EOL;
			echo $e->getTraceAsString() . PHP_EOL;
		}
	}
	echo sprintf('===============  %s | End CRON  ==========', date('Y-m-d H:i:s')) . PHP_EOL;
} else {
	echo('Access denied!');
}
