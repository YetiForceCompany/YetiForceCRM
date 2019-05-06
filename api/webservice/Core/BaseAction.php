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
	/** @var array Permitted modules */
	public $allowedMethod;

	/** @var \Api\Controller */
	public $controller;

	/** @var \App\Base */
	public $session;

	public function checkAction()
	{
		if ((isset($this->allowedMethod) && !in_array($this->controller->method, $this->allowedMethod)) || !method_exists($this, $this->controller->method)) {
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
		\Vtiger_Field_Model::setDefaultUiTypeClassName('\\Api\\Core\\Modules\\Vtiger\\UiTypes\\Base');
		$db->createCommand()
			->update($sessionTable, ['changed' => date('Y-m-d H:i:s')], ['id' => $this->session->get('id')])
			->execute();
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
	}

	/**
	 * Get current language.
	 *
	 * @return string
	 */
	public function getLanguage(): string
	{
		$language = '';
		if (!empty($this->controller->headers['accept-language'])) {
			$language = $this->controller->headers['accept-language'];
		} elseif ($this->session && !$this->session->isEmpty('sessionLanguage')) {
			$language = $this->session->get('sessionLanguage');
		} elseif ($this->session && !$this->session->isEmpty('language')) {
			$language = $this->session->get('language');
		}
		return $language;
	}

	/**
	 * Get permission type.
	 *
	 * @return int
	 */
	public function getPermissionType()
	{
		return $this->session->get('type');
	}

	/**
	 * Get crmid for portal user.
	 *
	 * @return int
	 */
	public function getUserCrmId()
	{
		return $this->session->get('crmid');
	}

	/**
	 * Get parent record.
	 *
	 * @return int
	 */
	public function getParentCrmId()
	{
		if ($this->controller && $parentId = (int) $this->controller->request->getHeader('x-parent-id')) {
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
