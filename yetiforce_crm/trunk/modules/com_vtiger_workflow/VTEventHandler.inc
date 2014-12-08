<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('include/events/SqlResultIterator.inc');
require_once('VTWorkflowManager.inc');
require_once('VTEntityCache.inc');

require_once 'include/Webservices/Utils.php';
require_once("modules/Users/Users.php");
require_once("include/Webservices/VtigerCRMObject.php");
require_once("include/Webservices/VtigerCRMObjectMeta.php");
require_once("include/Webservices/DataTransform.php");
require_once("include/Webservices/WebServiceError.php");
require_once 'include/utils/utils.php';
require_once 'include/Webservices/ModuleTypes.php';
require_once('include/Webservices/Retrieve.php');
require_once('include/Webservices/Update.php');
require_once 'include/Webservices/WebserviceField.php';
require_once 'include/Webservices/EntityMeta.php';
require_once 'include/Webservices/VtigerWebserviceObject.php';
require_once('VTWorkflowUtils.php');

/*
 * VTEventHandler
 */

class VTWorkflowEventHandler extends VTEventHandler {

	/**
	 * Push tasks to the task queue if the conditions are true
	 * @param $entityData A VTEntityData object representing the entity.
	 */
	function handleEvent($eventName, $entityData, $entityCache = false) {
		$util = new VTWorkflowUtils();
		$user = $util->adminUser();
		global $adb;
		$isNew = $entityData->isNew();

		if (!$entityCache) {
			$entityCache = new VTEntityCache($user);
		}

		$wsModuleName = $util->toWSModuleName($entityData);
		$wsId = vtws_getWebserviceEntityId($wsModuleName,
						$entityData->getId());
		$entityData = $entityCache->forId($wsId);

		/*
		 * Customer - Feature #10254 Configuring all Email notifications including Ticket notifications
		 * workflows are intialised from ModCommentsHandler.php
		 * While adding a comment on any record which are supporting Comments ModCommentsHandler will trigger
		 */
		if (!is_array($this->workflows)) {
			$wfs = new VTWorkflowManager($adb);
			$this->workflows = $wfs->getWorkflowsForModule($entityData->getModuleName());
		}
		$workflows = $this->workflows;

		foreach ($workflows as $workflow) {
			if (!is_a($workflow, 'Workflow'))
				continue;
			switch ($workflow->executionCondition) {
				case VTWorkflowManager::$ON_FIRST_SAVE: {
						if ($isNew) {
							$doEvaluate = true;
						} else {
							$doEvaluate = false;
						}
						break;
					}
				case VTWorkflowManager::$ONCE: {
						$entity_id = vtws_getIdComponents($entityData->getId());
						$entity_id = $entity_id[1];
						if ($workflow->isCompletedForRecord($entity_id)) {
							$doEvaluate = false;
						} else {
							$doEvaluate = true;
						}
						break;
					}
				case VTWorkflowManager::$ON_EVERY_SAVE: {
						$doEvaluate = true;
						break;
					}
				case VTWorkflowManager::$ON_MODIFY: {
						$doEvaluate = !($isNew);
						break;
					}
                case VTWorkflowManager::$MANUAL: {
						$doEvaluate = false;
						break;
					}
				case VTWorkflowManager::$ON_SCHEDULE:{
						$doEvaluate = false;
						break;
					}
				default: {
						throw new Exception("Should never come here! Execution Condition:" . $workflow->executionCondition);
					}
			}
			if ($doEvaluate && $workflow->evaluate($entityCache, $entityData->getId())) {
				if (VTWorkflowManager::$ONCE == $workflow->executionCondition) {
					$entity_id = vtws_getIdComponents($entityData->getId());
					$entity_id = $entity_id[1];
					$workflow->markAsCompletedForRecord($entity_id);
				}

				$workflow->performTasks($entityData);
			}
		}
		$util->revertUser();
	}

}

?>