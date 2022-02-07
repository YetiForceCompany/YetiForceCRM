<?php

/**
 * Verify user data action class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_VerifyData_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('recordPreSave');
		$this->exposeMethod('validatePassword');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser() && $currentUser->getId() != $request->getInteger('record')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Verify record pre save.
	 *
	 * @param \App\Request $request
	 */
	public function recordPreSave(App\Request $request): void
	{
		$message = '';
		$moduleName = $request->getModule();
		$checkUserName = false;
		$userId = $request->isEmpty('record', true) ? false : $request->getInteger('record');
		if (Users_Module_Model::checkMailExist($request->get('email'), $userId)) {
			$message = \App\Language::translate('LBL_USER_MAIL_EXIST', $moduleName);
		}
		if ($request->isEmpty('record', true)) {
			$checkUserName = true;
			if (!$request->isEmpty('password', true)) {
				$checkPassword = Settings_Password_Record_Model::checkPassword($request->getRaw('password'));
				if ($checkPassword) {
					$message = $checkPassword;
				}
			}
		} else {
			$recordModel = Vtiger_Record_Model::getInstanceById($userId, $moduleName);
			if ($request->get('userName') !== $recordModel->get('user_name')) {
				$checkUserName = true;
			}
		}
		if ($checkUserName && $checkUserName = Users_Module_Model::checkUserName($request->get('userName'), $userId)) {
			$message = $checkUserName;
		}
		$response = new Vtiger_Response();
		$response->setResult(['message' => $message]);
		$response->emit();
	}

	/**
	 * Validate user password.
	 *
	 * @param \App\Request $request
	 */
	public function validatePassword(App\Request $request): void
	{
		if (App\User::checkPreviousPassword($request->getInteger('record'), $request->getRaw('password'))) {
			$message = \App\Language::translate('ERR_PASSWORD_HAS_ALREADY_BEEN_USED', 'Other:Exceptions');
		} else {
			$message = Settings_Password_Record_Model::checkPassword($request->getRaw('password'));
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'type' => $message ? 'error' : 'success',
			'message' => $message ?: \App\Language::translate('LBL_USER_PASSWORD_IS_OK', $request->getModule()),
		]);
		$response->emit();
	}
}
