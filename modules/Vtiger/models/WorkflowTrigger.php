<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_WorkflowTrigger_Model
{

	public function execute($moduleName, $record, $ids, $userID)
	{
		vimport('~~modules/com_vtiger_workflow/VTEntityCache.inc');
		vimport('~~modules/com_vtiger_workflow/include.inc');
		vimport('~~include/Webservices/Utils.php');
		vimport('~~include/Webservices/Retrieve.php');

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$wsId = vtws_getWebserviceEntityId($moduleName, $record);
		$adb = PearDatabase::getInstance();
		$wfs = new VTWorkflowManager($adb);
		$entityCache = new VTEntityCache($currentUser);
		$entityData = $entityCache->forId($wsId);
		$entityData->executeUser = $userID;
		foreach ($ids as $id) {
			$workflow = $wfs->retrieve($id);
			if ($workflow->evaluate($entityCache, $entityData->getId())) {
				$workflow->performTasks($entityData);
			}
		}
	}
}
