<?php
/**
 * Mail signature delete action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Mail signature delete action class.
 */
class Settings_MailSignature_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$result = ['success' => false];
		$recordModel = Settings_MailSignature_Record_Model::getInstanceById($request->getInteger('record'));
		if ($request->getBoolean('detailView') && $recordModel->delete()) {
			$result = Settings_Vtiger_Module_Model::getInstance($request->getModule(false))->getDefaultUrl();
		} elseif ($recordModel) {
			$result = ['success' => (bool) $recordModel->delete()];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
