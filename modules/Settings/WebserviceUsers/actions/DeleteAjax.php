<?php

/**
 * Class to delete.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_WebserviceUsers_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$typeApi = $request->getByType('typeApi', 'Alnum');
		$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($recordId, $typeApi);
		$result = $recordModel->delete();

		$response = new Vtiger_Response();
		$response->setResult(['success' => $result]);
		$response->emit();
	}
}
