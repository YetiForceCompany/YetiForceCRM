<?php

/**
 * OSSMailScanner SaveCRMuser action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailScanner_SaveCRMuser_Action extends Vtiger_Action_Controller
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
		$value = $request->get('value');
		if ($userid) {
			$adb = PearDatabase::getInstance();
			$adb->pquery('update roundcube_users set crm_user_id = ? WHERE user_id = ?', [$value, $userid]);
			$success = true;
			$data = \App\Language::translate('JS_saveuser_info', 'OSSMailScanner');
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
