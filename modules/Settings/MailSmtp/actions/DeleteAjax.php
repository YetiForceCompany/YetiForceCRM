<?php

/**
 * MailSmtp delete action model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_MailSmtp_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$result = ['success' => false];
		$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($request->getInteger('record'));
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
