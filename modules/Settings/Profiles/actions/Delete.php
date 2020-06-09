<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Profiles_Delete_Action extends Settings_Vtiger_Basic_Action
{
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$transferRecordId = $request->getInteger('transfer_record');

		$recordModel = Settings_Profiles_Record_Model::getInstanceById($recordId);
		$transferToProfile = Settings_Profiles_Record_Model::getInstanceById($transferRecordId);
		if ($recordModel && $transferToProfile) {
			$recordModel->delete($transferToProfile);
		}

		$response = new Vtiger_Response();
		$result = ['success' => true];

		$response->setResult($result);
		$response->emit();
	}
}
