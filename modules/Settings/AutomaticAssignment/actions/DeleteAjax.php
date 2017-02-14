<?php

/**
 * Class to delete
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{

	/**
	 * Function  proccess
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');
		$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($recordId);
		$recordModel->delete();

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($recordModel->getId());
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
