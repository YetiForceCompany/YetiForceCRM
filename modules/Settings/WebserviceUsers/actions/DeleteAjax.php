<?php

/**
 * Class to delete
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceUsers_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{

	/**
	 * Function  proccess
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$typeApi = $request->get('typeApi');
		$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($recordId, $typeApi);
		$result = $recordModel->delete();

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($result);
		$responceToEmit->emit();
	}

	/**
	 * Validating incoming request.
	 * @param Vtiger_Request $request
	 */
	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateReadAccess();
	}
}
