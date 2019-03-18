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
	 * {@inheritdoc}
	 */
	public function loginRequired()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * Process action.
	 */
	public function process()
	{
		$mode = $this->request->has('mode') ? $this->request->getMode() : 'login';
		return $this->{$mode}();
	}

	/**
	 * Login function.
	 *
	 * @return \App\Response
	 */
	public function login()
	{
		$response = new \App\Response();
		$bfInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		if ($bfInstance->isActive() && $bfInstance->isBlockedIp()) {
			$bfInstance->incAttempts();
			\Users_Module_Model::getInstance('Users')->saveLoginHistory(strtolower($this->request->getByType('username', 'Text')), 'Blocked IP');
			$response->setError(401, 'LBL_TOO_MANY_FAILED_LOGIN_ATTEMPTS');
		} else {
			$userId = $this->getUser();
			$userModel = \App\User::getUserModel($userId);
			$method = $userModel->getDetail('login_method') ?? '';
			$auth = \App\Auth\Base::getInstance($userId, $method, $this->request);
			if ($result = $auth->verify()) {
				$response->setResult($result);
				$this->setSessionData($userModel, $result);
			} else {
				$response->setError(401, $auth->getMessage());
				if ($bfInstance->isActive()) {
					$bfInstance->updateBlockedIp();
					if ($bfInstance->isBlockedIp()) {
						$bfInstance->sendNotificationEmail();
						$response->setError(401, 'LBL_TOO_MANY_FAILED_LOGIN_ATTEMPTS');
					}
				}
				\Users_Module_Model::getInstance('Users')->saveLoginHistory(App\Purifier::encodeHtml($this->request->getRaw('username')), 'Failed login');
			}
		}

		return $response;
	}

	/**
	 * Set session data.
	 *
	 * @param App\User $userModel
	 * @param mixed    $result
	 */
	private function setSessionData(App\User $userModel, $result)
	{
		if (true === $result) {
			\App\Session::set('authenticated_user_id', $userModel->getId());
			\App\User::setCurrentUserId($userModel->getId());
			\App\Session::set('app_unique_key', \App\Config::main('application_unique_key'));
			\App\Session::set('user_name', $userModel->get('user_name'));
			\App\Session::set('full_user_name', $userModel->getName());
		}
		\App\Session::set('fingerprint', $this->request->get('fingerprint'));
		if ($this->request->has('loginLanguage') && \App\Config::main('langInLoginView')) {
			\App\Session::set('language', $this->request->getByType('loginLanguage'));
		}
	}

	/**
	 * Gets user ID.
	 *
	 * @return int
	 */
	private function getUser(): int
	{
		if (\App\Session::has('2faUserId')) {
			$userId = \App\Session::get('2faUserId');
		} else {
			$userName = $this->request->getByType('username', 'Text');
			$userId = (new App\Db\Query())->select(['id'])->from('vtiger_users')->where(['or', ['user_name' => $userName], ['user_name' => strtolower($userName)]])->limit(1)->scalar();
		}
		return (int) $userId;
	}
}
