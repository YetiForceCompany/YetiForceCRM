<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

use App\Purifier;

class Users_Login_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		if ($nonce = \App\Session::get('CSP_TOKEN')) {
			$this->headers->csp['script-src'] .= " 'nonce-{$nonce}'";
		}
		$this->headers->csp['default-src'] = '\'self\'';
		$this->headers->csp['script-src'] = str_replace([
			' \'unsafe-inline\'', ' blob:',
		], '', $this->headers->csp['script-src']);
		$this->headers->csp['form-action'] = '\'self\'';
		$this->headers->csp['style-src'] = '\'self\'';
		$this->headers->csp['base-uri'] = '\'self\'';
		$this->headers->csp['object-src'] = '\'none\'';
	}

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

	/** {@inheritdoc} */
	public function loginRequired()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		return true;
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		if ($bfInstance->isActive() && $bfInstance->isBlockedIp()) {
			$bfInstance->incAttempts();
			Users_Module_Model::getInstance('Users')->saveLoginHistory(strtolower($request->getByType('username', 'Text')), 'ERR_LOGIN_DESP_IP_BLOCK');
			header('location: index.php?module=Users&view=Login');
			return false;
		}
		if ('2fa' === \App\Session::get('LoginAuthyMethod')) {
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
	public function check2fa(App\Request $request): void
	{
		$userId = \App\Session::get('2faUserId');
		$authMethod = new Users_Totp_Authmethod($userId);
		if ($authMethod->verifyCode(\App\User::getUserModel($userId)->getDetail('authy_secret_totp'), $request->getByType('user_code', Purifier::DIGITS))) {
			\App\Session::set('authenticated_user_id', $userId);
			\App\Session::delete('2faUserId');
			\App\Session::delete('LoginAuthyMethod');
			$this->redirectUser();
		} else {
			\App\Session::set('UserLoginMessage', \App\Language::translate('LBL_2FA_WRONG_CODE', 'Users'));
			\App\Session::set('UserLoginMessageType', 'error');
			$this->failedLogin($request, '2fa');
		}
	}

	/**
	 * Redirect the user to a different page.
	 */
	private function redirectUser(): void
	{
		if ($param = ($_SESSION['return_params'] ?? false)) {
			unset($_SESSION['return_params']);
			header('location: index.php?' . $param);
		} elseif (App\Config::performance('SHOW_ADMIN_PANEL') && $this->userModel->isAdmin()) {
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
	 * @return void
	 */
	public function login(App\Request $request): void
	{
		$userName = $request->getByType('username', 'Text');
		$password = $request->getRaw('password');
		if ('install' === $request->getMode()) {
			$this->cleanInstallationFiles();
		}
		$this->userRecordModel = Users_Record_Model::getCleanInstance('Users')->set('user_name', $userName);
		if (!empty($password) && $this->userRecordModel->doLogin($password)) {
			$this->userModel = App\User::getUserModel($this->userRecordModel->getId());
			$this->afterLogin($request);
			Users_Module_Model::getInstance('Users')->saveLoginHistory(strtolower($userName), 'Signed in'); //Track the login History
			if ($this->isMultiFactorAuthentication() && !Users_Totp_Authmethod::mustInit($this->userRecordModel->getId())) {
				header('location: index.php');
			} else {
				$this->redirectUser();
			}
			return;
		}
		\App\Session::set('UserLoginMessage', App\Language::translate('LBL_INVALID_USER_OR_PASSWORD', 'Users'));
		$this->failedLogin($request, 'login');
	}

	/**
	 * After login function.
	 *
	 * @param \App\Request $request
	 */
	public function afterLogin(App\Request $request): void
	{
		\App\Controller\Headers::generateCspToken();
		if (\Config\Security::$loginSessionRegenerate) {
			\App\Session::regenerateId(true); // to overcome session id reuse.
		}
		if ($this->isMultiFactorAuthentication()) {
			if (Users_Totp_Authmethod::mustInit($this->userRecordModel->getId())) {
				\App\Session::set('authenticated_user_id', $this->userRecordModel->getId());
				\App\Process::addEvent([
					'name' => 'ShowAuthy2faModal',
					'priority' => 7,
					'execution' => 'TOTP_OPTIONAL' === \App\Config::security('USER_AUTHY_MODE') ? 'once' : 'constant',
					'type' => 'modal',
					'url' => 'index.php?module=Users&view=TwoFactorAuthenticationModal&record=' . $this->userRecordModel->getId(),
				]);
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
		\App\Session::set('app_unique_key', App\Config::main('application_unique_key'));
		\App\Session::set('user_name', $this->userRecordModel->get('user_name'));
		\App\Session::set('full_user_name', $this->userModel->getName());
		\App\Session::set('fingerprint', $request->getByType('fingerprint', Purifier::ALNUM2));
		\App\Session::set('user_agent', \App\Request::_getServer('HTTP_USER_AGENT', ''));

		$eventHandler = new \App\EventHandler();
		$eventHandler->setRecordModel($this->userRecordModel);
		$eventHandler->setParams(['userModel' => $this->userModel, 'password' => $request->getRaw('password')]);
		$eventHandler->setModuleName('Users');
		$eventHandler->trigger('UsersAfterLogin');

		if ($request->has('loginLanguage') && App\Config::main('langInLoginView')) {
			\App\Session::set('language', $request->getByType('loginLanguage'));
		}
		if ($request->has('layout')) {
			\App\Session::set('layout', $request->getByType('layout'));
		}
		if ($this->userModel->isAdmin() && \App\Config::security('askAdminAboutVisitPurpose', true)) {
			\App\Process::addEvent([
				'name' => 'showVisitPurpose',
				'type' => 'modal',
				'url' => 'index.php?module=Users&view=VisitPurpose',
			]);
		}
		if (\App\YetiForce\Shop::verify(false, true)) {
			\App\Process::addEvent([
				'name' => 'YetiForceShopAlert',
				'type' => 'modal',
				'execution' => 'once',
				'url' => 'index.php?module=Users&view=YetiForce&view=YetiForce&mode=shop',
			]);
		}
		if (!empty(\Config\Main::$showRegistrationAlert) && !\App\YetiForce\Register::isRegistered()) {
			\App\Process::addEvent([
				'name' => 'YetiForceRegistrationAlert',
				'type' => 'modal',
				'execution' => 'once',
				'url' => 'index.php?module=Users&view=YetiForce&view=YetiForce&mode=registration',
			]);
		}
	}

	/**
	 * Check whether to run multi-factor authentication.
	 *
	 * @return bool
	 */
	private function isMultiFactorAuthentication(): bool
	{
		return Users_Totp_Authmethod::isActive($this->userRecordModel->getId()) && !\in_array(\App\RequestUtil::getRemoteIP(true), \App\Config::security('whitelistIp2fa', []));
	}

	/**
	 * Clean installation files.
	 */
	public function cleanInstallationFiles(): void
	{
		\vtlib\Functions::recurseDelete('install');
		\vtlib\Functions::recurseDelete('public_html/install');
		\vtlib\Functions::recurseDelete('tests');
		\vtlib\Functions::recurseDelete('.github');
		\vtlib\Functions::recurseDelete('.gitattributes');
		\vtlib\Functions::recurseDelete('.gitignore');
		\vtlib\Functions::recurseDelete('.travis.yml');
		\vtlib\Functions::recurseDelete('codecov.yml');
		\vtlib\Functions::recurseDelete('.gitlab-ci.yml');
		\vtlib\Functions::recurseDelete('.php_cs.dist');
		\vtlib\Functions::recurseDelete('.scrutinizer.yml');
		\vtlib\Functions::recurseDelete('.sensiolabs.yml');
		\vtlib\Functions::recurseDelete('.prettierrc.js');
		\vtlib\Functions::recurseDelete('.editorconfig');
		\vtlib\Functions::recurseDelete('.whitesource');
		\vtlib\Functions::recurseDelete('whitesource.config.json');
		\vtlib\Functions::recurseDelete('jsconfig.json');
		\vtlib\Functions::recurseDelete('sonar-project.properties');
		\vtlib\Functions::recurseDelete('docker-compose.yml');
		\vtlib\Functions::recurseDelete('Dockerfile');
		\vtlib\Functions::recurseDelete('crowdin.yml');
	}

	/**
	 * Failed login function.
	 *
	 * @param \App\Request $request
	 * @param string       $type
	 */
	public function failedLogin(App\Request $request, string $type): void
	{
		$status = '2fa' === $type ? 'ERR_WRONG_2FA_CODE' : 'Failed login';
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		if ($bfInstance->isActive()) {
			$bfInstance->updateBlockedIp();
			if ($bfInstance->isBlockedIp()) {
				$bfInstance->sendNotificationEmail();
				\App\Session::set('UserLoginMessage', App\Language::translate('LBL_TOO_MANY_FAILED_LOGIN_ATTEMPTS', 'Users'));
				\App\Session::set('UserLoginMessageType', 'error');
				$status = '2fa' === $type ? 'ERR_2FA_IP_BLOCK' : 'ERR_LOGIN_IP_BLOCK';
			}
		}
		$userName = $request->getRaw('username');
		if (!$userName) {
			$userName = \App\Session::get('user_name');
		}
		Users_Module_Model::getInstance('Users')->saveLoginHistory(Purifier::encodeHtml($userName), $status);
		header('location: index.php?module=Users&view=Login');
	}
}
