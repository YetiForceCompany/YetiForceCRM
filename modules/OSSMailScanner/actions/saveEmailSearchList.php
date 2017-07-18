<?php

/**
 * OSSMailScanner saveEmailSearchList action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailScanner_saveEmailSearchList_Action extends Vtiger_Action_Controller
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
		$vale = $request->get('vale');
		if (!empty($vale)) {
			$vale = implode(',', $vale);
		}
		$OSSMailScannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$OSSMailScannerModel->setEmailSearchList($vale);
		$success = true;
		$data = \App\Language::translate('JS_save_fields_info', 'OSSMailScanner');
		$result = array('success' => $success, 'data' => $data);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
