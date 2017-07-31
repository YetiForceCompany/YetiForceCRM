<?php

/**
 * OSSMailScanner SaveActions action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailScanner_SaveActions_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$userid = $request->get('userid');
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
		$result = array('success' => $success, 'data' => $data);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
