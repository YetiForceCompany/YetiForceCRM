<?php

/**
 * Settings TreesManager delete action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_Delete_Action extends Settings_Vtiger_Index_Action
{
	public function process(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$recordModel = Settings_TreesManager_Record_Model::getInstanceById($recordId);
		$recordModel->delete();
		$returnUrl = $recordModel->getListViewUrl();
		$response = new Vtiger_Response();
		$response->setResult($returnUrl);

		return $response;
	}
}
