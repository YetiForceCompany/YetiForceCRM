<?php
/**
 * OSSMailScanner SaveCRMuser action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */

/**
 * OSSMailScanner SaveCRMuser action class
 */
class OSSMailScanner_SaveCRMuser_Action extends Vtiger_Action_Controller
{

	/**
	 * Check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$userid = $request->get('userid');
		$value = $request->get('value');
		if ($userid) {
			\App\Db::getInstance()->createCommand()->update('roundcube_users', ['crm_user_id' => $value], ['user_id' => $userid])->execute();
			$success = true;
			$data = \App\Language::translate('JS_saveuser_info', 'OSSMailScanner');
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
