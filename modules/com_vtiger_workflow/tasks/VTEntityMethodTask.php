<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */
require_once 'modules/com_vtiger_workflow/VTEntityMethodManager.php';

class VTEntityMethodTask extends VTTask
{
	public $executeImmediately = true;

	/** {@inheritdoc} */
	public $recordEventState = self::RECORD_EVENT_DOUBLE_MODE;

	public function getFieldNames()
	{
		return ['methodName'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel = null)
	{
		if (!$recordModel) {
			$moduleName = Settings_Workflows_Record_Model::getInstance($this->workflowId)->get('module_name');
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		}
		(new VTEntityMethodManager())->executeMethod($recordModel, $this->methodName);
	}
}
