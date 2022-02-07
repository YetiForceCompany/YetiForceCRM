<?php

/**
 * OSSMailScanner restart cron action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_RestartCron_Action extends \App\Controller\Action
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
		OSSMailScanner_Record_Model::runRestartCron($request->getInteger('scanId'));
		$result = ['success' => true, 'data' => \App\Language::translate('JS_info_restart_ok', 'OSSMailScanner')];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
