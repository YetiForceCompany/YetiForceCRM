<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_CronTasks_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordModel = Settings_CronTasks_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule(false));
		$fieldsList = $recordModel->getModule()->getEditableFieldsList();
		foreach ($fieldsList as $fieldName) {
			$fieldValue = $request->getByType($fieldName, 'Alnum');
			if (isset($fieldValue)) {
				$recordModel->set($fieldName, $fieldValue);
			}
		}
		$recordModel->save();
		$response = new Vtiger_Response();
		$response->setResult([true]);
		$response->emit();
	}
}
