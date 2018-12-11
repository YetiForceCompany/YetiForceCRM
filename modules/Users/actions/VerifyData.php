<?php

/**
 * Verify user data action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_VerifyData_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser() && $currentUser->getId() != $request->getInteger('record')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
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
}
