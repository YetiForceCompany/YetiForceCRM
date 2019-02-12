<?php

/**
 * Class to delete.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceUsers_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Function  proccess.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$typeApi = $request->getByType('typeApi', 'Alnum');
		$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($recordId, $typeApi);
		$result = $recordModel->delete();

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($result);
		$responceToEmit->emit();
	}
}
