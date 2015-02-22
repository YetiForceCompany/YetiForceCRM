<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
chdir (dirname(__FILE__) . '/../');
/**
 * Start the cron services configured.
 */
include_once 'vtlib/Vtiger/Cron.php';
require_once 'config/config.php';
require_once('modules/Emails/mail.php');
// Extended inclusions
require_once 'include/Loader.php';
vimport ('include.runtime.EntryPoint');

$version = explode('.', phpversion());
$php = ($version[0] * 10000 + $version[1] * 100 + $version[2]);

if($php <  50300){
    $hostName = php_uname('n');
} else {
    $hostName = gethostname();
}

if(PHP_SAPI === "cli" || (isset($_SESSION["authenticated_user_id"]) &&	isset($_SESSION["app_unique_key"]) && $_SESSION["app_unique_key"] == $application_unique_key)){

$cronTasks = false;
if (isset($_REQUEST['service'])) {
	// Run specific service
	$cronTasks = array(Vtiger_Cron::getInstance($_REQUEST['service']));
}
else {
	// Run all service
	$cronTasks = Vtiger_Cron::listAllActiveInstances();
}

$cronRunId = microtime(true);
$cronStarts = date('Y-m-d H:i:s');

//set global current user permissions
global $current_user;
$current_user = Users::getActiveAdminUser();

echo sprintf('[CRON],"%s",%s,Instance,"%s","",[STARTS]',$cronRunId,$site_URL,$cronStarts)."\n";
foreach ($cronTasks as $cronTask) {
	try {
		$cronTask->setBulkMode(true);

		// Not ready to run yet?
		if (!$cronTask->isRunnable()) {
			echo sprintf("[INFO] %s - not ready to run as the time to run again is not completed\n", $cronTask->getName());
			continue;
		}

		// Timeout could happen if intermediate cron-tasks fails
		// and affect the next task. Which need to be handled in this cycle.				
		if ($cronTask->hadTimedout()) {
			echo sprintf("[INFO] %s - cron task had timedout as it is not completed last time it run- restarting\n", $cronTask->getName());	
		}
		
		// Mark the status - running		
		$cronTask->markRunning();
		echo sprintf('[CRON],"%s",%s,%s,"%s","",[STARTS]',$cronRunId,$site_URL,$cronTask->getName(),date('Y-m-d H:i:s',$cronTask->getLastStart()))."\n";
		
        checkFileAccess($cronTask->getHandlerFile());		
		require_once $cronTask->getHandlerFile();
		
		// Mark the status - finished
		$cronTask->markFinished();
		echo "\n".sprintf('[CRON],"%s",%s,%s,"%s","%s",[ENDS]',$cronRunId,$site_URL,$cronTask->getName(),date('Y-m-d H:i:s',$cronTask->getLastStart()),date('Y-m-d H:i:s',$cronTask->getLastEnd()))."\n";
        
	} catch (Exception $e) {
		echo sprintf("[ERROR]: %s - cron task execution throwed exception.\n", $cronTask->getName());
		echo $e->getMessage();
		echo "\n";
	}		
}

$cronEnds = date('Y-m-d H:i:s');
echo sprintf('[CRON],"%s",%s,Instance,"%s","%s",[ENDS]',$cronRunId,$site_URL,$cronStarts,$cronEnds)."\n";

}

else{
    echo("Access denied!");
}



?>
