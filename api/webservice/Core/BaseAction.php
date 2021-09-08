<?php
/**
 * Base action file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	protected $userData = [];

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
		$this->userData['type'] = (int) $this->userData['type'];
		$this->userData['custom_params'] = \App\Json::isEmpty($this->userData['custom_params']) ? [] : \App\Json::decode($this->userData['custom_params']);
		if ($this->userData['auth']) {
			$this->userData['auth'] = \App\Json::decode(\App\Encryption::getInstance()->decrypt($this->userData['auth']));
		}
		\App\User::setCurrentUserId($this->userData['user_id']);
		$userModel = \App\User::getCurrentUserModel();
		$userModel->set('permission_type', $this->userData['type']);
		$userModel->set('permission_crmid', $this->userData['crmid']);
		$userModel->set('permission_app', (int) $this->controller->app['id']);
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
		$this->userData = (new \App\Db\Query())->select(["$userTable.*", 'sid' => "$sessionTable.id", "$sessionTable.language", "$sessionTable.created", "$sessionTable.changed", "$sessionTable.params"])
			->from($userTable)
			->innerJoin($sessionTable, "$sessionTable.user_id = $userTable.id")
			->where(["$sessionTable.id" => $this->controller->headers['x-token'], "$userTable.status" => 1])
			->one(\App\Db::getInstance('webservice'));
		if (empty($this->userData)) {
			throw new \Api\Core\Exception('Invalid token', 401);
		}
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
		if ((strtotime('now') > strtotime($this->userData['created']) + (\Config\Security::$apiLifetimeSessionCreate * 60)) || (strtotime('now') > strtotime($this->userData['changed']) + (\Config\Security::$apiLifetimeSessionUpdate * 60))) {
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
		if (!empty($this->userData['language'])) {
			$language = $this->userData['language'];
		} elseif (!empty($this->userData['custom_params']['language'])) {
			$language = $this->userData['custom_params']['language'];
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
		return $this->userData['type'];
	}

	/**
	 * Get crmid for portal user.
	 *
	 * @return int
	 */
	public function getUserCrmId(): int
	{
		return $this->userData['crmid'];
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
	 * Get information, whether to check inventory levels.
	 *
	 * @return bool
	 */
	public function getCheckStockLevels(): bool
	{
		$parentId = \Api\Portal\Privilege::USER_PERMISSIONS !== $this->getPermissionType() ? $this->getParentCrmId() : 0;
		return empty($parentId) || (bool) \Vtiger_Record_Model::getInstanceById($parentId)->get('check_stock_levels');
	}

	/**
	 * Get parent record.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return int
	 */
	public function getParentCrmId(): int
	{
		if ($this->controller && ($parentId = $this->controller->request->getHeader('x-parent-id'))) {
			$hierarchy = new \Api\Portal\BaseModule\Hierarchy();
			$hierarchy->setUserData($this->userData);
			$hierarchy->findId = $parentId;
			$hierarchy->moduleName = \App\Record::getType(\App\Record::getParentRecord($this->getUserCrmId()));
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
	public function setUserData(array $data): void
	{
		$this->userData = \App\Utils::merge($this->userData, $data);
	}

	/**
	 * Get user data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getUserData(string $key)
	{
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
		$data['last_method'] = $this->controller->request->getServer('REQUEST_URI');
		$data['agent'] = \App\TextParser::textTruncate($this->controller->request->getServer('HTTP_USER_AGENT', '-'), 100, false);
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
		$this->userData['custom_params']['agent'] = \App\TextParser::textTruncate($this->controller->request->getServer('HTTP_USER_AGENT', '-'), 100, false);
		if (isset($data['custom_params'])) {
			$data['custom_params'] = \App\Json::encode(\App\Utils::merge(($this->userData['custom_params'] ?? []), $data['custom_params']));
		}
		if (isset($data['auth'])) {
			$data['auth'] = \App\Encryption::getInstance()->encrypt(\App\Json::encode(\App\Utils::merge(($this->userData['auth'] ?? []), $data['auth'])));
		}
		\App\Db::getInstance('webservice')->createCommand()
			->update($this->controller->app['tables']['user'], $data, ['id' => $this->userData['id']])
			->execute();
	}
}
