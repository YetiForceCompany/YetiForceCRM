<?php

/**
 * Reset password action class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Users_Password_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
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
	public function checkPermission(App\Request $request): bool
	{
		if ('demo' === App\Config::main('systemMode')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		switch ($request->getMode()) {
			case 'reset':
				if (true === $currentUserModel->isAdminUser() || (int) $currentUserModel->get('id') === $request->getInteger('record')) {
					return true;
				}
				break;
			case 'change':
				if (\App\User::getCurrentUserId() === \App\User::getCurrentUserRealId() && (true === $currentUserModel->isAdminUser() || (int) $currentUserModel->get('id') === $request->getInteger('record'))) {
					return true;
				}
				break;
			case 'massReset':
				if (true === $currentUserModel->isAdminUser()) {
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
	 *
	 * @return void
	 */
	public function reset(App\Request $request): void
	{
		$moduleName = $request->getModule();
		$password = \App\Encryption::generateUserPassword();
		$userRecordModel = Users_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$userRecordModel->set('changeUserPassword', true);
		$userRecordModel->set('user_password', $password);
		$userRecordModel->set('date_password_change', date('Y-m-d H:i:s'));

		$eventHandler = new \App\EventHandler();
		$eventHandler->setRecordModel($userRecordModel);
		$eventHandler->setModuleName('Users');
		$eventHandler->setParams(['action' => 'reset']);
		$eventHandler->trigger('UsersBeforePasswordChange');

		$userRecordModel->save();

		$eventHandler->trigger('UsersAfterPasswordChange');

		$expirationDate = date('Y-m-d H:i:s', strtotime('+24 hour'));
		$token = \App\Utils\Tokens::generate('Users_LoginForgotPassword_Action', [$userRecordModel->getId()], $expirationDate);
		\App\Mailer::sendFromTemplate([
			'template' => 'UsersResetPassword',
			'moduleName' => 'Users',
			'recordId' => $userRecordModel->getId(),
			'to' => $userRecordModel->get('email1'),
			'url' => \Config\Main::$site_URL . 'index.php?module=Users&view=LoginPassChange&token=' . $token,
			'expirationDate' => \App\Fields\DateTime::formatToDisplay($expirationDate),
			'token' => $token,
		]);
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_WAS_RESET_AND_SENT_TO_USER', 'Users')]]);
		$response->emit();
	}

	/**
	 * Change user password.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function change(App\Request $request): void
	{
		$moduleName = $request->getModule();
		$password = $request->getRaw('password');
		$userRecordModel = Users_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$response = new Vtiger_Response();
		$isOtherUser = App\User::getCurrentUserId() !== $request->getInteger('record');
		if (!$isOtherUser && 'PASSWORD' !== \App\Session::get('UserAuthMethod')) {
			$response->setResult(['processStop' => true, 'notify' => ['text' => \App\Language::translate('LBL_NOT_CHANGE_PASS_AUTH_EXTERNAL_SYSTEM', $moduleName), 'type' => 'error']]);
		} elseif ($password !== $request->getRaw('confirm_password')) {
			$response->setResult(['processStop' => true, 'notify' => ['text' => \App\Language::translate('LBL_PASSWORD_SHOULD_BE_SAME', $moduleName), 'type' => 'error']]);
		} elseif (!$isOtherUser && !$userRecordModel->verifyPassword($request->getRaw('oldPassword'))) {
			$response->setResult(['processStop' => true, 'notify' => ['text' => \App\Language::translate('LBL_INCORRECT_OLD_PASSWORD', $moduleName), 'type' => 'error']]);
		} else {
			$userRecordModel->set('changeUserPassword', true);
			$userRecordModel->set('user_password', $password);
			$userRecordModel->set('date_password_change', date('Y-m-d H:i:s'));
			if ($userRecordModel->getId() === \App\User::getCurrentUserRealId()) {
				$userRecordModel->set('force_password_change', 0);
			}
			try {
				$eventHandler = new \App\EventHandler();
				$eventHandler->setRecordModel($userRecordModel);
				$eventHandler->setModuleName($moduleName);
				$eventHandler->setParams(['action' => 'change']);
				$eventHandler->trigger('UsersBeforePasswordChange');
				$userRecordModel->save();
				$eventHandler->trigger('UsersAfterPasswordChange');

				$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_SUCCESSFULLY_CHANGED', $moduleName)]]);
				if (\App\Session::has('ShowUserPasswordChange')) {
					\App\Session::delete('ShowUserPasswordChange');
					\App\Process::removeEvent('ShowUserPasswordChange');
				}
			} catch (\App\Exceptions\SaveRecord $exc) {
				$response->setResult(['processStop' => true, 'notify' => ['text' => \App\Language::translateSingleMod($exc->getMessage(), 'Other.Exceptions'), 'type' => 'error']]);
			} catch (\App\Exceptions\Security $exc) {
				$response->setResult(['processStop' => true, 'notify' => ['text' => $exc->getMessage(), 'type' => 'error']]);
			}
		}
		$response->emit();
	}

	/**
	 * Mass reset user password.
	 *
	 * @param \App\Request $request
	 */
	public function massReset(App\Request $request): void
	{
		$moduleName = $request->getModule();
		$recordsList = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		foreach ($recordsList as $userId) {
			$password = \App\Encryption::generateUserPassword();
			$userRecordModel = Users_Record_Model::getInstanceById($userId, $moduleName);
			$userRecordModel->set('changeUserPassword', true);
			$userRecordModel->set('user_password', $password);
			$userRecordModel->set('date_password_change', date('Y-m-d H:i:s'));

			$eventHandler = new \App\EventHandler();
			$eventHandler->setRecordModel($userRecordModel);
			$eventHandler->setModuleName('Users');
			$eventHandler->setParams(['action' => 'massReset']);
			$eventHandler->trigger('UsersBeforePasswordChange');

			$userRecordModel->save();

			$eventHandler->trigger('UsersAfterPasswordChange');

			$expirationDate = date('Y-m-d H:i:s', strtotime('+24 hour'));
			$token = \App\Utils\Tokens::generate('Users_LoginForgotPassword_Action', [$userRecordModel->getId()], $expirationDate);
			\App\Mailer::sendFromTemplate([
				'template' => 'UsersResetPassword',
				'moduleName' => 'Users',
				'recordId' => $userRecordModel->getId(),
				'to' => $userRecordModel->get('email1'),
				'url' => \Config\Main::$site_URL . 'index.php?module=Users&view=LoginPassChange&token=' . $token,
				'expirationDate' => \App\Fields\DateTime::formatToDisplay($expirationDate),
				'token' => $token,
			]);
		}
		$response = new Vtiger_Response();
		$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_WAS_RESET_AND_SENT_TO_USERS', 'Users')]]);
		$response->emit();
	}
}
