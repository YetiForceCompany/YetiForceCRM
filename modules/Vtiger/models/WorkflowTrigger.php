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

	public static function execute($moduleName, $record, $ids, $userID)
	{
		vimport('~~modules/com_vtiger_workflow/include.php');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$wfs = new VTWorkflowManager();
		foreach ($ids as $id) {
			$workflow = $wfs->retrieve($id);
			if ($workflow->evaluate($recordModel)) {
				$workflow->performTasks($recordModel);
			}
		}
	}
}
