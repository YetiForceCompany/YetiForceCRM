<?php

/**
 * Settings WAPRO ERP save ajax action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings WAPRO ERP save ajax action class.
 */
class Settings_Wapro_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			$recordModel = Settings_Wapro_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_Wapro_Record_Model::getInstanceById($request->getInteger('record'));
		}
		$recordModel->setDataFromRequest($request);
		$response = new Vtiger_Response();

		$verify = App\Integrations\Wapro::verifyDatabaseAccess($recordModel->get('server'), $recordModel->get('database'), $recordModel->get('username'), $recordModel->get('password'), $recordModel->get('port'));
		if ($verify['status']) {
			$response->setResult($recordModel->save());
		} else {
			$response->setError($verify['code'] ?: 500, nl2br($verify['message']));
		}
		$response->emit();
	}
}
