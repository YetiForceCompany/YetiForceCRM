<?php

/**
 * Reset password action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_ResetPassword_Action extends Vtiger_Action_Controller
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser() === true || (AppConfig::security('SHOW_MY_PREFERENCES') && (int) $currentUserModel->get('id') === $request->getInteger('record'))) {
			return true;
		}
		throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$userRecordModel = Users_Record_Model::getInstanceById(App\User::getCurrentUserId(), $moduleName);
		$password = \App\Encryption::getRandomPassword();
		$userRecordModel->set('user_password', $password);
		$userRecordModel->save();

		\App\Mailer::sendFromTemplate([
			'template' => 'UsersResetPassword',
			'moduleName' => $moduleName,
			'recordId' => $userRecordModel->getId(),
			'to' => $userRecordModel->get('email1'),
			'password' => $password,
		]);
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_WAS_RESET_AND_SENT_TO_USER', 'Users')]]);
		$response->emit();
	}
}
