<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Workflows_DeleteAjax_Action extends Settings_Vtiger_Index_Action
{
	public function process(App\Request $request)
	{
		$qualifiedModule = $request->getModule(false);
		$recordId = $request->getInteger('record');
		$response = new Vtiger_Response();
		$recordModel = Settings_Workflows_Record_Model::getInstance($recordId);
		if ($recordModel->isDefault()) {
			$response->setResult([
				'success' => false,
				'message' => \App\Language::translate('LBL_CANNOT_DELETE_DEFAULT_WORKFLOW', $qualifiedModule)
			]);
		} else {
			$recordModel->delete();
			$response->setResult(['success' => 'ok']);
		}
		$response->emit();
	}
}
