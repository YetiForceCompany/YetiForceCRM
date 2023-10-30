<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Settings_Groups_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$transferRecordId = $request->getInteger('transfer_record');

		$recordModel = Settings_Groups_Record_Model::getInstance($recordId);
		if (\App\User::isExists($transferRecordId)) {
			$transferToOwner = Users_Record_Model::getInstanceById($transferRecordId, 'Users');
		} else {
			$transferToOwner = Settings_Groups_Record_Model::getInstance($transferRecordId);
		}
		if ($recordModel && $transferToOwner) {
			$recordModel->delete($transferToOwner);
		}

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
