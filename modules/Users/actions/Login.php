<?php
/**
 * Login action file.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Modules\Users\Actions;

/**
 * Login action class.
 */
class Login extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public $loginRequired = false;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$bfInstance = \Settings_BruteForce_Module_Model::getCleanInstance();
		if ($bfInstance->isActive() && $bfInstance->isBlockedIp()) {
			$bfInstance->incAttempts();
			\Users_Module_Model::getInstance('Users')->saveLoginHistory(strtolower($this->request->getByType('username', 'Text')), 'Blocked IP');
			$this->response->setError(401, 'LBL_TOO_MANY_FAILED_LOGIN_ATTEMPTS');
			return false;
		}
		$userId = $this->getLoggedUserId();
		$userModel = \App\User::getUserModel($userId);
		$method = $userModel->getDetail('login_method') ?? '';
		$auth = \App\Auth\Base::getInstance($userId, $method, $this->request);
		if ($result = $auth->verify()) {
			$this->response->setResult($result);
			$this->setSessionData($userModel, $result);
		} else {
			$this->response->setError(new \Exception($auth->getMessage(), 401));
			if ($bfInstance->isActive()) {
				$bfInstance->updateBlockedIp();
				if ($bfInstance->isBlockedIp()) {
					$bfInstance->sendNotificationEmail();
					$this->response->setError(401, 'LBL_TOO_MANY_FAILED_LOGIN_ATTEMPTS');
				}
			}
			\Users_Module_Model::getInstance('Users')->saveLoginHistory(\App\Purifier::encodeHtml($this->request->getRaw('username')), 'Failed login');
		}
	}

	/**
	 * Set session data.
	 *
	 * @param \App\User $userModel
	 * @param mixed     $result
	 */
	private function setSessionData(\App\User $userModel, $result)
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
	 * {@inheritdoc}
	 */
	protected function getLoggedUserId(): int
	{
		if (\App\Session::has('2faUserId')) {
			$userId = \App\Session::get('2faUserId');
		} else {
			$userName = $this->request->getByType('username', 'Text');
			$userId = (new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['or', ['user_name' => $userName], ['user_name' => strtolower($userName)]])->limit(1)->scalar();
		}
		return (int) $userId;
	}
}
