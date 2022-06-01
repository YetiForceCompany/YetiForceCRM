<?php
/**
 * Base action file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core;

/**
 * Base action class.
 */
class BaseAction
{
	/** @var string[] Allowed methods */
	public $allowedMethod;

	/** @var array Allowed headers */
	public $allowedHeaders = [];

	/** @var \Api\Controller */
	public $controller;

	/** @var string Response data type. */
	public $responseType = 'data';

	/** @var array User data */
	private $userData = [];

	/**
	 * Check called action.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return void
	 */
	public function checkAction(): void
	{
		if ((isset($this->allowedMethod) && !\in_array($this->controller->method, $this->allowedMethod)) || !method_exists($this, $this->controller->method)) {
			throw new \Api\Core\Exception('Invalid method', 405);
		}
		$this->checkPermission();
		$this->checkPermissionToModule();
	}

	/**
	 * Check permission to module.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return void
	 */
	protected function checkPermissionToModule(): void
	{
		if (!$this->controller->request->isEmpty('module') && !Module::checkModuleAccess($this->controller->request->get('module'))) {
			throw new \Api\Core\Exception('No permissions for module', 403);
		}
	}

	/**
	 * Check permission to method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return void
	 */
	protected function checkPermission(): void
	{
		if (empty($this->controller->headers['x-token'])) {
			throw new \Api\Core\Exception('No sent token', 401);
		}
		$this->loadSession();
		$this->checkLifetimeSession();
		\App\User::setCurrentUserId($this->getUserData('user_id'));
		$userModel = \App\User::getCurrentUserModel();
		$userModel->set('permission_type', $this->getPermissionType());
		$userModel->set('permission_crmid', $this->getUserCrmId());
		$userModel->set('permission_app', $this->controller->app['id']);
		$namespace = $this->controller->app['type'];
		\App\Privilege::setPermissionInterpreter("\\Api\\{$namespace}\\Privilege");
		\App\PrivilegeQuery::setPermissionInterpreter("\\Api\\{$namespace}\\PrivilegeQuery");
		$this->updateSession();
	}

	/**
	 * Load user session data .
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return void
	 */
	protected function loadSession(): void
	{
		$sessionTable = $this->controller->app['tables']['session'];
		$userTable = $this->controller->app['tables']['user'];
		$userData = (new \App\Db\Query())->select(["$userTable.*", 'sid' => "$sessionTable.id", "$sessionTable.language", "$sessionTable.created", "$sessionTable.changed", "$sessionTable.params"])
			->from($userTable)
			->innerJoin($sessionTable, "$sessionTable.user_id = $userTable.id")
			->where(["$sessionTable.id" => $this->controller->headers['x-token'], "$userTable.status" => 1])
			->one(\App\Db::getInstance('webservice'));
		if (!$userData) {
			throw new \Api\Core\Exception('Invalid token', 401);
		}
		$this->setAllUserData($userData);
	}

	/**
	 * Check lifetime user session.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return void
	 */
	protected function checkLifetimeSession(): void
	{
		if ((strtotime('now') > strtotime($this->getUserData('created')) + (\Config\Security::$apiLifetimeSessionCreate * 60)) || (strtotime('now') > strtotime($this->getUserData('changed')) + (\Config\Security::$apiLifetimeSessionUpdate * 60))) {
			\App\Db::getInstance('webservice')->createCommand()
				->delete($this->controller->app['tables']['session'], ['id' => $this->controller->headers['x-token']])
				->execute();
			throw new \Api\Core\Exception('Token has expired', 401);
		}
	}

	/**
	 * Pre process function.
	 */
	public function preProcess()
	{
		$language = $this->getLanguage();
		if ($language) {
			\App\Language::setTemporaryLanguage($language);
		}
		if (\App\Config::performance('CHANGE_LOCALE')) {
			\App\Language::initLocale();
		}
	}

	/**
	 * Get current language.
	 *
	 * @return string
	 */
	public function getLanguage(): string
	{
		$language = '';
		if ($userLang = $this->getUserData('language')) {
			$language = $userLang;
		} elseif ($userLang = $this->getUserData('custom_params', 'language')) {
			$language = $userLang;
		} elseif (!empty($this->controller->headers['accept-language'])) {
			$language = str_replace('_', '-', \Locale::acceptFromHttp($this->controller->headers['accept-language']));
		} else {
			$language = \App\Config::main('default_language');
		}
		return $language;
	}

	/**
	 * Get permission type.
	 *
	 * @return int
	 */
	public function getPermissionType(): int
	{
		return (int) $this->userData['type'];
	}

	/**
	 * Get crmid for portal user.
	 *
	 * @return int
	 */
	public function getUserCrmId(): int
	{
		return $this->userData['crmid'] ?: 0;
	}

	/**
	 * Get user storage ID.
	 *
	 * @return int
	 */
	public function getUserStorageId(): ?int
	{
		return $this->userData['istorage'] ?? null;
	}

	/**
	 * Get parent record.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return int|null
	 */
	public function getParentCrmId(): ?int
	{
		if ($this->controller && ($parentId = $this->controller->request->getHeader('x-parent-id'))) {
			$hierarchy = new \Api\WebservicePremium\BaseModule\Hierarchy();
			$hierarchy->setAllUserData($this->userData);
			$hierarchy->findId = $parentId;
			$parentRecord = \App\Record::getParentRecord($this->getUserCrmId());
			$hierarchy->moduleName = $parentRecord ? '' : \App\Record::getType($parentRecord);
			$records = $hierarchy->get();
			if (isset($records[$parentId])) {
				return $parentId;
			}
			throw new \Api\Core\Exception('No permission to X-PARENT-ID', 403);
		}
		return \App\Record::getParentRecord($this->getUserCrmId());
	}

	/**
	 * Set user data.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function setAllUserData(array $data): void
	{
		$this->userData = \App\Utils::merge($this->userData, $data);
	}

	/**
	 * Set user data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function setUserData(string $key, $value): void
	{
		if ('custom_params' === $key || 'preferences' === $key) {
			if (!\is_array($this->userData[$key])) {
				$this->userData[$key] = \App\Json::isEmpty($this->userData[$key]) ? [] : \App\Json::decode($this->userData[$key]);
			}
			$this->userData[$key] = \App\Utils::merge($this->userData[$key], $value);
		} else {
			$this->userData[$key] = $value;
		}
	}

	/**
	 * Get user data and session data.
	 *
	 * @param string $key
	 * @param string $param
	 *
	 * @return mixed
	 */
	public function getUserData(string $key, string $param = '')
	{
		if (!isset($this->userData[$key])) {
			return null;
		}
		if ('custom_params' === $key || 'preferences' === $key) {
			if (!\is_array($this->userData[$key])) {
				$this->userData[$key] = \App\Json::isEmpty($this->userData[$key]) ? [] : \App\Json::decode($this->userData[$key]);
			}
			if ($param) {
				return $this->userData[$key][$param] ?? null;
			}
		} elseif ('auth' === $key) {
			if (!\is_array($this->userData[$key])) {
				$this->userData[$key] = empty($this->userData['auth']) ? [] : \App\Json::decode(\App\Encryption::getInstance()->decrypt($this->userData['auth']));
			}
		}
		return $this->userData[$key] ?? null;
	}

	/**
	 * Update user session.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function updateSession(array $data = []): void
	{
		if (empty($this->userData['sid'])) {
			return;
		}
		$data['changed'] = date('Y-m-d H:i:s');
		$data['ip'] = $this->controller->request->getServer('REMOTE_ADDR');
		$data['parent_id'] = $this->controller->request->getHeader('x-parent-id') ?: 0;
		$data['last_method'] = $this->controller->request->getServer('REQUEST_URI');
		$data['agent'] = \App\TextUtils::textTruncate($this->controller->request->getServer('HTTP_USER_AGENT', '-'), 100, false);
		\App\Db::getInstance('webservice')->createCommand()
			->update($this->controller->app['tables']['session'], $data, ['id' => $this->userData['sid']])
			->execute();
	}

	/**
	 * Update user data.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function updateUser(array $data = []): void
	{
		if (!\is_array($this->userData['custom_params'])) {
			$this->userData['custom_params'] = \App\Json::isEmpty($this->userData['custom_params']) ? [] : \App\Json::decode($this->userData['custom_params']);
		}
		$this->userData['custom_params']['agent'] = \App\TextUtils::textTruncate($this->controller->request->getServer('HTTP_USER_AGENT', '-'), 100, false);
		if (isset($data['custom_params'])) {
			$data['custom_params'] = \App\Json::encode(\App\Utils::merge($this->userData['custom_params'], $data['custom_params']));
		}
		if (isset($data['auth'])) {
			$data['auth'] = \App\Encryption::getInstance()->encrypt(\App\Json::encode(\App\Utils::merge(($this->getUserData('auth') ?? []), $data['auth'])));
		}
		if (isset($data['preferences'])) {
			if (!\is_array($this->userData['preferences'])) {
				$this->userData['preferences'] = \App\Json::isEmpty($this->userData['preferences']) ? [] : \App\Json::decode($this->userData['preferences']);
			}
			$data['preferences'] = \App\Json::encode(\App\Utils::merge($this->userData['preferences'], $data['preferences']));
		}
		\App\Db::getInstance('webservice')->createCommand()
			->update($this->controller->app['tables']['user'], $data, ['id' => $this->userData['id']])
			->execute();
	}
}
