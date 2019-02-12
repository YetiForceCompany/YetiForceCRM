<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_WorkflowTrigger_Model
{
	/**
	 * Function executes workflow tasks.
	 *
	 * @param string $moduleName
	 * @param int    $record
	 * @param array  $ids
	 * @param int    $userId
	 */
	public static function execute($moduleName, $record, $ids, $userId)
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		if ($userId) {
			$recordModel->executeUser = $userId;
		}
		$wfs = new VTWorkflowManager();
		foreach ($ids as $id) {
			$workflow = $wfs->retrieve($id);
			if ($workflow->evaluate($recordModel)) {
				$workflow->performTasks($recordModel);
			}
		}
	}
}
