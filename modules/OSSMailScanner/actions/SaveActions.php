<?php

/**
 * OSSMailScanner SaveActions action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$userid = $request->getInteger('userid');
		$vale = $request->get('vale');
		if ($userid) {
			if ($vale != 'null') {
				$vale = implode(',', $vale);
			}
			$OSSMailScannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
			$OSSMailScannerModel->setActions($userid, $vale);
			$success = true;
			$data = \App\Language::translate('JS_save_info', 'OSSMailScanner');
		} else {
			$success = false;
			$data = 'Error: Brak userid';
		}
		$result = ['success' => $success, 'data' => $data];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
