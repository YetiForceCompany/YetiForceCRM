<?php

/**
 * OSSMailScanner SaveWidgetConfig action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_SaveWidgetConfig_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(App\Request $request)
	{
		$param = $request->get('name');
		$val = $request->get('value');
		$conf_type = $request->get('conf_type');
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$recordModel->setConfigWidget($conf_type, $param, $val);
		$result = ['success' => true, 'data' => \App\Language::translate('JS_save_config_info', 'OSSMailScanner')];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
