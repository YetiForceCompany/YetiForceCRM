<?php
/**
 * OSSMailScanner SaveCRMuser action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * OSSMailScanner SaveCRMuser action class.
 */
class OSSMailScanner_SaveCRMuser_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('user');
		$this->exposeMethod('status');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Update mail user.
	 *
	 * @param \App\Request $request
	 */
	public function user(App\Request $request)
	{
		$moduleName = $request->getModule();
		$userId = $request->getInteger('userid');
		if ($userId) {
			\App\Db::getInstance()->createCommand()
				->update('roundcube_users', ['crm_user_id' => $request->getInteger('value')], ['user_id' => $userId])
				->execute();
			$success = true;
			$data = \App\Language::translate('JS_saveuser_info', $moduleName);
		} else {
			$success = false;
			$data = \App\Language::translate('LBL_NO_DATA', $moduleName);
		}
		$result = ['success' => $success, 'data' => $data];
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Update mail status.
	 *
	 * @param \App\Request $request
	 */
	public function status(App\Request $request)
	{
		$moduleName = $request->getModule();
		$userId = $request->getInteger('userid');
		$status = $request->getInteger('status');
		if (!\in_array($status, [OSSMail_Record_Model::MAIL_BOX_STATUS_ACTIVE,  OSSMail_Record_Model::MAIL_BOX_STATUS_DISABLED])) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $status, 406);
		}
		if ($userId) {
			OSSMail_Record_Model::setAccountUserData($userId, ['crm_status' => $status]);
			$success = true;
			$data = \App\Language::translate('JS_saveuser_info', $moduleName);
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
