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
include_once 'include/main/WebUI.php';

Vtiger_Session::init();
$authenticatedUserId = Vtiger_Session::get('authenticated_user_id');
$appUniqueKey = Vtiger_Session::get('app_unique_key');
$user = (!empty($authenticatedUserId) && !empty($appUniqueKey) && $appUniqueKey == AppConfig::main('application_unique_key'));
if (PHP_SAPI === 'cli' || PHP_SAPI === 'cgi-fcgi' || PHP_SAPI === 'ucgi5' || $user) {
	$cronTasks = false;
	vtlib\Cron::setCronAction(true);
	if (AppRequest::has('service')) {
		// Run specific service
		$cronTasks = [vtlib\Cron::getInstance(AppRequest::get('service'))];
	} else {
		// Run all service
		$cronTasks = vtlib\Cron::listAllActiveInstances();
	}

	$cronStart = microtime(true);
	//set global current user permissions
	App\User::setCurrentUserId(Users::getActiveAdminId());
	$current_user = Users::getActiveAdminUser();
	vglobal('current_user', $current_user);
	if ($user) {
		echo '<pre>';
	}
	echo sprintf('---------------  %s | Start CRON  ----------', date('Y-m-d H:i:s')) . PHP_EOL;
	foreach ($cronTasks as $cronTask) {
		try {
			\App\Log::trace($cronTask->getName() . ' - Start');
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
				\App\Log::trace($cronTask->getName() . ' - Task omitted, it has not been finished during the last scanning');
				echo sprintf('%s | %s - Task omitted, it has not been finished during the last scanning' . PHP_EOL, date('Y-m-d H:i:s'), $cronTask->getName());
				continue;
			}

			// Not ready to run yet?
			if (!$cronTask->isRunnable()) {
				\App\Log::trace($cronTask->getName() . ' - Not ready to run as the time to run again is not completed');
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
				\App\Log::warning($cronTask->getName() . ' - The task returned a message:' . PHP_EOL . $taskResponse);
				echo 'Task response:' . PHP_EOL . $taskResponse . PHP_EOL;
			}

			// Mark the status - finished
			$cronTask->markFinished();
			echo sprintf('%s | %s - End task (%s s)', date('Y-m-d H:i:s'), $cronTask->getName(), $taskTime) . PHP_EOL;
			\App\Log::trace($cronTask->getName() . ' - End');
		} catch (\Exception\AppException $e) {
			echo sprintf('%s | ERROR: %s - Cron task execution throwed exception.', date('Y-m-d H:i:s'), $cronTask->getName()) . PHP_EOL;
			echo $e->getMessage() . PHP_EOL;
			echo $e->getTraceAsString() . PHP_EOL;
			if (AppConfig::main('systemMode') === 'test') {
				throw $e;
			}
		}
	}
	echo sprintf('===============  %s (' . round(microtime(true) - $cronStart, 2) . ') | End CRON  ==========', date('Y-m-d H:i:s')) . PHP_EOL;
} else {
	echo('Access denied!');
}
file_put_contents('user_privileges/cron.php', '<?php $sapi=\'' . PHP_SAPI . '\';$ini=\'' . php_ini_loaded_file() . '\';$log=\'' . ini_get('error_log') . '\';$vphp=\'' . PHP_VERSION . '\';');

