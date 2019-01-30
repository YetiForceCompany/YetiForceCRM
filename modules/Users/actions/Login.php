<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Users_Login_Action extends \App\Controller\Action
{
	/**
	 * Users record model.
	 *
	 * @var Users_Record_Model
	 */
	private $userRecordModel;

	/**
	 * Base user model.
	 *
	 * @var \App\User
	 */
	private $userModel;

	/**
	 * {@inheritdoc}
	 */
	public function loginRequired()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		if ($bfInstance->isActive() && $bfInstance->isBlockedIp()) {
			$bfInstance->incAttempts();
			Users_Module_Model::getInstance('Users')->saveLoginHistory(strtolower($request->getByType('username', 'Text')), 'Blocked IP');
			header('location: index.php?module=Users&view=Login');
			return false;
		}
		if (\App\Session::get('LoginAuthyMethod') === '2fa') {
			$this->check2fa($request);
		} else {
			$this->login($request);
		}
	}

	/**
	 * Check if 2FA verification is necessary.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function check2fa(\App\Request $request)
	{
		$userId = \App\Session::get('2faUserId');
		if (Users_Totp_Authmethod::verifyCode(\App\User::getUserModel($userId)->getDetail('authy_secret_totp'), $request->getByType('user_code', 'Digital'))) {
			\App\Session::set('authenticated_user_id', $userId);
			\App\Session::delete('2faUserId');
			\App\Session::delete('LoginAuthyMethod');
			$this->redirectUser();
		} else {
			\App\Session::set('UserLoginMessage', \App\Language::translate('LBL_2FA_WRONG_CODE', 'Users'));
			\App\Session::set('UserLoginMessageType', 'error');
			$this->failedLogin($request);
		}
	}

	/**
	 * Redirect the user to a different page.
	 */
	private function redirectUser()
	{
		if (isset($_SESSION['return_params'])) {
			header('location: index.php?' . $_SESSION['return_params']);
		} elseif (AppConfig::performance('SHOW_ADMIN_PANEL') && $this->userModel->isAdmin()) {
			header('location: index.php?module=Vtiger&parent=Settings&view=Index');
		} else {
			header('location: index.php');
		}
	}

	/**
	 * User login to the system.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool
	 */
	public function login(\App\Request $request)
	{
		$userName = $request->getByType('username', 'Text');
		$password = $request->getRaw('password');
		if ($request->getMode() === 'install') {
			$this->cleanInstallationFiles();
		}
		$this->userRecordModel = Users_Record_Model::getCleanInstance('Users')->set('user_name', $userName);
		if (!empty($password) && $this->userRecordModel->doLogin($password)) {
			$this->userModel = App\User::getUserModel($this->userRecordModel->getId());
			if (\App\Session::get('UserAuthMethod') === 'PASSWORD' && $this->userRecordModel->verifyPasswordChange($this->userModel)) {
				\App\Session::set('UserLoginMessage', App\Language::translate('LBL_YOUR_PASSWORD_HAS_EXPIRED', 'Users'));
				\App\Session::set('UserLoginMessageType', 'error');
				header('location: index.php');
				return true;
			}
			$this->afterLogin($request);
			Users_Module_Model::getInstance('Users')->saveLoginHistory(strtolower($userName)); //Track the login History
			if (Users_Totp_Authmethod::isActive($this->userRecordModel->getId()) && !Users_Totp_Authmethod::mustInit($this->userRecordModel->getId())) {
				header('location: index.php?module=Users&view=Login');
			} else {
				$this->redirectUser();
			}
			return true;
		}
		\App\Session::set('UserLoginMessage', App\Language::translate('LBL_INVALID_USER_OR_PASSWORD', 'Users'));
		$this->failedLogin($request);
	}

	/**
	 * After login function.
	 *
	 * @param \App\Request $request
	 */
	public function afterLogin(\App\Request $request)
	{
		if (AppConfig::main('session_regenerate_id')) {
			\App\Session::regenerateId(true); // to overcome session id reuse.
		}
		if (Users_Totp_Authmethod::isActive($this->userRecordModel->getId())) {
			if (Users_Totp_Authmethod::mustInit($this->userRecordModel->getId())) {
				\App\Session::set('authenticated_user_id', $this->userRecordModel->getId());
				\App\Session::set('ShowAuthy2faModal', true);
			} else {
				\App\Session::set('LoginAuthyMethod', '2fa');
				\App\Session::set('2faUserId', $this->userRecordModel->getId());
				if (\App\Session::has('UserLoginMessage')) {
					\App\Session::delete('UserLoginMessage');
				}
			}
		} else {
			\App\Session::set('authenticated_user_id', $this->userRecordModel->getId());
		}
		\App\Session::set('app_unique_key', AppConfig::main('application_unique_key'));
		\App\Session::set('user_name', $this->userRecordModel->get('user_name'));
		\App\Session::set('full_user_name', $this->userModel->getName());
		\App\Session::set('fingerprint', $request->get('fingerprint'));
		if ($request->has('loginLanguage') && AppConfig::main('langInLoginView')) {
			\App\Session::set('language', $request->getByType('loginLanguage'));
		}
		if ($request->has('layout')) {
			\App\Session::set('layout', $request->getByType('layout'));
		}
	}

	/**
	 * Clean installation files.
	 */
	public function cleanInstallationFiles()
	{
		\vtlib\Functions::recurseDelete('install');
		\vtlib\Functions::recurseDelete('public_html/install');
		\vtlib\Functions::recurseDelete('tests');
		\vtlib\Functions::recurseDelete('.github');
		\vtlib\Functions::recurseDelete('.gitattributes');
		\vtlib\Functions::recurseDelete('.gitignore');
		\vtlib\Functions::recurseDelete('.travis.yml');
		\vtlib\Functions::recurseDelete('.codecov.yml');
		\vtlib\Functions::recurseDelete('.gitlab-ci.yml');
		\vtlib\Functions::recurseDelete('.php_cs.dist');
		\vtlib\Functions::recurseDelete('.scrutinizer.yml');
		\vtlib\Functions::recurseDelete('.sensiolabs.yml');
	}

	/**
	 * Failed login function.
	 *
	 * @param \App\Request $request
	 */
	public function failedLogin(\App\Request $request)
	{
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		if ($bfInstance->isActive()) {
			$bfInstance->updateBlockedIp();
			if ($bfInstance->isBlockedIp()) {
				$bfInstance->sendNotificationEmail();
				\App\Session::set('UserLoginMessage', App\Language::translate('LBL_TOO_MANY_FAILED_LOGIN_ATTEMPTS', 'Users'));
				\App\Session::set('UserLoginMessageType', 'error');
			}
		}
		Users_Module_Model::getInstance('Users')->saveLoginHistory(App\Purifier::encodeHtml($request->getRaw('username')), 'Failed login');
		header('location: index.php?module=Users&view=Login');
	}
}
