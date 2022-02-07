<?php

/**
 * OSSMailScanner SaveActions action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_SaveActions_Action extends \App\Controller\Action
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
		$moduleName = $request->getModule();
		$userId = $request->getInteger('userid');
		$vale = $request->getArray('vale');
		if ($userId) {
			$vale = implode(',', $vale);
			$OSSMailScannerModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$OSSMailScannerModel->setActions($userId, $vale);
			$success = true;
			$data = \App\Language::translate('JS_save_info', $moduleName);
		} else {
			$success = false;
			$data = \App\Language::translate('LBL_NO_DATA', $moduleName);
		}
		$result = ['success' => $success, 'data' => $data];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
