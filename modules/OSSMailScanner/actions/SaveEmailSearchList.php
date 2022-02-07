<?php

/**
 * OSSMailScanner save email search list action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_SaveEmailSearchList_Action extends \App\Controller\Action
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
		$vale = $request->get('vale');
		if (!empty($vale)) {
			$vale = implode(',', $vale);
		}
		$mailScannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$mailScannerModel->setEmailSearchList($vale);
		$result = ['success' => true, 'data' => \App\Language::translate('JS_save_fields_info', 'OSSMailScanner')];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
