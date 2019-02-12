<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'include/Webservices/Utils.php';
require_once 'include/Webservices/WebServiceError.php';
require_once 'include/utils/VtlibUtils.php';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
require_once 'modules/com_vtiger_workflow/include.php';
require_once 'modules/com_vtiger_workflow/WorkFlowScheduler.php';

$workflowScheduler = new WorkFlowScheduler();
$workflowScheduler->queueScheduledWorkflowTasks();
$readyTasks = (new VTTaskQueue())->getReadyTasks();
$tm = new VTTaskManager();
foreach ($readyTasks as $taskDetails) {
	list($taskId, $entityId, $taskContents) = $taskDetails;
	$task = $tm->retrieveTask($taskId);
	//If task is not there then continue
	if (empty($task)) {
		continue;
	}
	$task->setContents($taskContents);
	$task->doTask(Vtiger_Record_Model::getInstanceById($entityId));
}
