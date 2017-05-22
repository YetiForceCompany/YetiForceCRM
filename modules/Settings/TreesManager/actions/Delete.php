<?php

/**
 * Settings TreesManager delete action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_TreesManager_Delete_Action extends Settings_Vtiger_Index_Action
{

	public function process(\App\Request $request)
	{
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_TreesManager_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		$recordModel->delete();
		$returnUrl = $recordModel->getListViewUrl();
		$response = new Vtiger_Response();
		$response->setResult($returnUrl);
		return $response;
	}
}
