<?php

/**
 * Reset password action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_Password_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('reset');
		$this->exposeMethod('change');
		$this->exposeMethod('massReset');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if (AppConfig::main('systemMode') === 'demo') {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		switch ($request->getMode()) {
			case 'reset':
			case 'change':
				if ($currentUserModel->isAdminUser() === true || (int) $currentUserModel->get('id') === $request->getInteger('record')) {
					return true;
				}
				break;
			case 'massReset':
				if ($currentUserModel->isAdminUser() === true) {
					return true;
				}
				break;
			default:
				break;
		}
		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
	}

	/**
	 * Reset user password.
	 *
	 * @param \App\Request $request
	 */
	public function reset(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$password = \App\Encryption::generateUserPassword();
		$userRecordModel = Users_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$userRecordModel->set('changeUserPassword', true);
		$userRecordModel->set('user_password', $password);
		$userRecordModel->set('date_password_change', date('Y-m-d H:i:s'));
		$userRecordModel->set('force_password_change', 0);
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

	/**
	 * Change user password.
	 *
	 * @param \App\Request $request
	 */
	public function change(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$password = $request->getRaw('password');
		$userRecordModel = Users_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$response = new Vtiger_Response();
		$isOtherUser = App\User::getCurrentUserId() !== $request->getInteger('record');
		if (!$isOtherUser && \App\Session::get('UserAuthMethod') !== 'PASSWORD') {
			$response->setResult(['procesStop' => true, 'notify' => ['text' => \App\Language::translate('LBL_NOT_CHANGE_PASS_AUTH_EXTERNAL_SYSTEM', 'Users'), 'type' => 'error']]);
		} elseif ($password !== $request->getRaw('confirmPassword')) {
			$response->setResult(['procesStop' => true, 'notify' => ['text' => \App\Language::translate('LBL_PASSWORD_SHOULD_BE_SAME', 'Users'), 'type' => 'error']]);
		} elseif (!$isOtherUser && !$userRecordModel->verifyPassword($request->getRaw('oldPassword'))) {
			$response->setResult(['procesStop' => true, 'notify' => ['text' => \App\Language::translate('LBL_INCORRECT_OLD_PASSWORD', 'Users'), 'type' => 'error']]);
		} else {
			$userRecordModel->set('changeUserPassword', true);
			$userRecordModel->set('user_password', $password);
			$userRecordModel->set('date_password_change', date('Y-m-d H:i:s'));
			$userRecordModel->set('force_password_change', $isOtherUser ? 1 : 0);
			try {
				$userRecordModel->save();
				$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_SUCCESSFULLY_CHANGED', 'Users')]]);
				\App\Session::delete('ShowUserPasswordChange');
			} catch (\App\Exceptions\SaveRecord $exc) {
				$response->setResult(['procesStop' => true, 'notify' => ['text' => \App\Language::translateSingleMod($exc->getMessage(), 'Other.Exceptions'), 'type' => 'error']]);
			} catch (\App\Exceptions\Security $exc) {
				$response->setResult(['procesStop' => true, 'notify' => ['text' => $exc->getMessage(), 'type' => 'error']]);
			}
		}
		$response->emit();
	}

	/**
	 * Mass reset user password.
	 *
	 * @param \App\Request $request
	 */
	public function massReset(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordsList = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		foreach ($recordsList as $userId) {
			$password = \App\Encryption::generateUserPassword();
			$userRecordModel = Users_Record_Model::getInstanceById($userId, $moduleName);
			$userRecordModel->set('changeUserPassword', true);
			$userRecordModel->set('user_password', $password);
			$userRecordModel->set('date_password_change', date('Y-m-d H:i:s'));
			$userRecordModel->set('force_password_change', 0);
			$userRecordModel->save();
			\App\Mailer::sendFromTemplate([
				'template' => 'UsersResetPassword',
				'moduleName' => $moduleName,
				'recordId' => $userRecordModel->getId(),
				'to' => $userRecordModel->get('email1'),
				'password' => $password,
			]);
		}
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_WAS_RESET_AND_SENT_TO_USERS', 'Users')]]);
		$response->emit();
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
