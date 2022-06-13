<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_PickListDependency_DeleteAjax_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Method for delete dependency.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function process(App\Request $request)
	{
		$recordModel = Settings_PickListDependency_Record_Model::getInstanceById($request->getInteger('record'));
		$response = new Vtiger_Response();
		try {
			$result = $recordModel->delete();
			$response->setResult(['success' => $result]);
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
