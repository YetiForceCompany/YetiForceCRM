<?php

/**
 * Save Application.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceUsers_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * Save.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$data = $request->get('param');
		$typeApi = $request->get('typeApi');
		$recordId = $request->get('record');
		if ($recordId) {
			$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($recordId, $typeApi);
		} else {
			$recordModel = Settings_WebserviceUsers_Record_Model::getCleanInstance($typeApi);
		}
		$result = $recordModel->save($data);

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($result);
		$responceToEmit->emit();
	}
}
