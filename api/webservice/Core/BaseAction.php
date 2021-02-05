<?php

namespace Api\Core;

/**
 * Base action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class BaseAction
{
	/** @var array Allowed method */
	public $allowedMethod;
	/** @var array Allowed headers */
	public $allowedHeaders = [];
	/** @var \Api\Controller */
	public $controller;
	/** @var \App\Base */
	public $session;
	/**
	 * Response data type.
	 *
	 * @var string
	 */
	public $responseType = 'data';

	/**
	 * Check called action.
	 *
	 * @return bool
	 */
	public function checkAction()
	{
		if ((isset($this->allowedMethod) && !\in_array($this->controller->method, $this->allowedMethod)) || !method_exists($this, $this->controller->method)) {
			throw new \Api\Core\Exception('Invalid method', 405);
		}
		$this->checkPermission();
		$this->checkPermissionToModule();
		return true;
	}

	/**
	 * Check permission to module.
	 *
	 * @throws \Api\Core\Exception
	 */
	public function checkPermissionToModule()
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
	 * @return bool
	 */
	public function checkPermission()
	{
		if (empty($this->controller->headers['x-token'])) {
			throw new \Api\Core\Exception('No sent token', 401);
		}
		$apiType = strtolower($this->controller->app['type']);
		$sessionTable = "w_#__{$apiType}_session";
		$userTable = "w_#__{$apiType}_user";
		$db = \App\Db::getInstance('webservice');
		$row = (new \App\Db\Query())->select([
			"$userTable.*",
			"$sessionTable.id",
			'sessionLanguage' => "$sessionTable.language",
			"$sessionTable.created",
			"$sessionTable.changed",
			"$sessionTable.params"
		])->from($userTable)
			->innerJoin($sessionTable, "$sessionTable.user_id = $userTable.id")
			->where(["$sessionTable.id" => $this->controller->headers['x-token'], "$userTable.status" => 1])
			->one($db);
		if (empty($row)) {
			throw new \Api\Core\Exception('Invalid token', 401);
		}
		if ((strtotime('now') > strtotime($row['created']) + (\Config\Security::$apiLifetimeSessionCreate * 60)) || (strtotime('now') > strtotime($row['changed']) + (\Config\Security::$apiLifetimeSessionUpdate * 60))) {
			$db->createCommand()
				->delete($sessionTable, ['id' => $this->controller->headers['x-token']])
				->execute();
			throw new \Api\Core\Exception('Token has expired', 401);
		}
		$this->session = new \App\Base();
		$this->session->setData($row);
		\App\User::setCurrentUserId($this->session->get('user_id'));
		$userModel = \App\User::getCurrentUserModel();
		$userModel->set('permission_type', $row['type']);
		$userModel->set('permission_crmid', $row['crmid']);
		$userModel->set('permission_app', $this->controller->app['id']);
		$namespace = ucfirst($apiType);
		\App\Privilege::setPermissionInterpreter("\\Api\\{$namespace}\\Privilege");
		\App\PrivilegeQuery::setPermissionInterpreter("\\Api\\{$namespace}\\PrivilegeQuery");
		$db->createCommand()->update($sessionTable, ['changed' => date('Y-m-d H:i:s')], ['id' => $this->session->get('id')])->execute();
		return true;
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
		if ($this->session && !$this->session->isEmpty('sessionLanguage')) {
			$language = $this->session->get('sessionLanguage');
		} elseif ($this->session && !$this->session->isEmpty('language')) {
			$language = $this->session->get('language');
		} elseif (!empty($this->controller->headers['accept-language'])) {
			$language = str_replace('_', '-', \Locale::acceptFromHttp($this->controller->headers['accept-language']));
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
		return $this->session->get('type');
	}

	/**
	 * Get crmid for portal user.
	 *
	 * @return int
	 */
	public function getUserCrmId(): int
	{
		return $this->session->get('crmid');
	}

	/**
	 * Get user storage ID.
	 *
	 * @return int
	 */
	public function getUserStorageId(): ?int
	{
		return $this->session->get('istorage');
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
	 * @return int
	 */
	public function getParentCrmId()
	{
		if ($this->controller && ($parentId = $this->controller->request->getHeader('x-parent-id'))) {
			$hierarchy = new \Api\Portal\BaseModule\Hierarchy();
			$hierarchy->session = $this->session;
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
}
