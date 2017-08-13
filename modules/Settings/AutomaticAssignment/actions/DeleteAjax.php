<?php

/**
 * Class to delete
 * @package YetiForce.Settings.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_AutomaticAssignment_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{

	/**
	 * Function  proccess
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordId = $request->get('record');
		$recordModel = Settings_AutomaticAssignment_Record_Model::getInstanceById($recordId);
		$recordModel->delete();

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($recordModel->getId());
		$responceToEmit->emit();
	}

	/**
	 * Validating incoming request.
	 * @param \App\Request $request
	 */
	public function validateRequest(\App\Request $request)
	{
		$request->validateReadAccess();
	}
}
