<?php
/**
 * Class to delete.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class to delete.
 */
class Settings_SMSNotifier_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Function  proccess.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$result = true;
		$recordModel = Settings_SMSNotifier_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule(false));
		if ($recordModel) {
			$result = (bool) $recordModel->delete();
		}
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult(['success' => $result]);
		$responceToEmit->emit();
	}
}
