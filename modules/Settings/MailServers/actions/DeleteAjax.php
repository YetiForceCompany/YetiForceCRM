<?php

/**
 * Delete action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Delete action class.
 */
class Settings_MailServers_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Function  process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$result = true;
		$recordModel = Settings_MailServers_Record_Model::getInstanceById($request->getInteger('record'));
		if ($recordModel->getId()) {
			$result = (bool) $recordModel->delete();
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => $result]);
		$response->emit();
	}
}
