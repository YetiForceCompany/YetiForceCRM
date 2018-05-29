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
		$moduleName = $request->getModule();
		$userName = $request->get('username');
		$password = $request->getRaw('password');
		$moduleModel = Users_Module_Model::getInstance('Users');
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		if ($bfInstance->isActive() && $bfInstance->isBlockedIp()) {
			$bfInstance->incAttempts();
			$moduleModel->saveLoginHistory(strtolower($userName), 'Blocked IP');
			header('Location: index.php?module=Users&view=Login');
			return false;
		}
		if ($request->getMode('mode') === 'install') {
			$this->cleanInstallationFiles();
		}

		$this->userRecordModel = Users_Record_Model::getCleanInstance('Users')->set('user_name', $userName);
		if (!empty($password) && $this->userRecordModel->doLogin($password)) {
			$this->userModel = App\User::getUserModel($this->userRecordModel->getId());
			if (\App\Session::get('UserAuthMethod') === 'PASSWORD' && $this->userRecordModel->verifyPasswordChange($this->userModel)) {
				\App\Session::set('UserLoginMessage', App\Language::translate('LBL_YOUR_PASSWORD_HAS_EXPIRED', $moduleName));
				\App\Session::set('UserLoginMessageType', 'error');
				header('Location: index.php');
				return true;
			}

			$this->afterLogin($request);
			$moduleModel->saveLoginHistory(strtolower($userName)); //Track the login History
			if (isset($_SESSION['return_params'])) {
				header('Location: index.php?' . $_SESSION['return_params']);
			} elseif (AppConfig::performance('SHOW_ADMIN_PANEL') && $this->userModel->isAdmin()) {
				header('Location: index.php?module=Vtiger&parent=Settings&view=Index');
			} else {
				header('Location: index.php');
			}
			return true;
		}
		\App\Session::set('UserLoginMessage', App\Language::translate('LBL_INVALID_USER_OR_PASSWORD', $moduleName));
		if ($bfInstance->isActive()) {
			$bfInstance->updateBlockedIp();
			if ($bfInstance->isBlockedIp()) {
				$bfInstance->sendNotificationEmail();
				\App\Session::set('UserLoginMessage', App\Language::translate('LBL_TOO_MANY_FAILED_LOGIN_ATTEMPTS', $moduleName));
				\App\Session::set('UserLoginMessageType', 'error');
			}
		}
		$moduleModel->saveLoginHistory(App\Purifier::encodeHtml($request->getRaw('username')), 'Failed login'); //Track the login History
		header('Location: index.php?module=Users&view=Login');
	}

	/**
	 * After login function.
	 *
	 * @param \App\Request $request
	 */
	public function afterLogin(\App\Request $request)
	{
		if (AppConfig::main('session_regenerate_id')) {
			App\Session::regenerateId(true); // to overcome session id reuse.
		}
		App\Session::set('authenticated_user_id', $this->userRecordModel->getId());
		App\Session::set('app_unique_key', AppConfig::main('application_unique_key'));
		App\Session::set('user_name', $this->userRecordModel->get('user_name'));
		App\Session::set('full_user_name', $this->userModel->getName());
		App\Session::set('fingerprint', $request->get('fingerprint'));
		if ($request->has('loginLanguage') && AppConfig::main('langInLoginView')) {
			App\Session::set('language', $request->getByType('loginLanguage'));
		}
		if ($request->has('layout')) {
			App\Session::set('layout', $request->getByType('layout'));
		}
	}

	/**
	 * Clean installation files.
	 */
	public function cleanInstallationFiles()
	{
		foreach (glob('languages/*/Install.php') as $path) {
			unlink($path);
		}
		\vtlib\Functions::recurseDelete('install');
		\vtlib\Functions::recurseDelete('public_html/install');
		\vtlib\Functions::recurseDelete('tests');
		\vtlib\Functions::recurseDelete('config/config.template.php');
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
}
