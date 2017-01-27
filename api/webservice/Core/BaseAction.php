<?php
namespace Api\Core;

/**
 * Base action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		$this->checkPermissionToModule();
		$this->checkPermission();
		/*
		  $acceptableUrl = $this->controller->app['acceptable_url'];
		  if ($acceptableUrl && rtrim($this->controller->app['acceptable_url'], '/') != rtrim($params['fromUrl'], '/')) {
		  throw new \Api\Core\Exception('LBL_INVALID_SERVER_URL', 401);
		  }
		 */
		return true;
	}

	/**
	 * Check permission to module
	 * @throws \Api\Core\Exception
	 */
	public function checkPermissionToModule()
	{
		if (!$this->controller->request->isEmpty('module') && !Module::checkModuleAccess($this->controller->request->get('module'))) {
			throw new \Api\Core\Exception('No permissions for module', 403);
		}
	}

	/**
	 * Check permission to method
	 * @return boolean
	 * @throws \Api\Core\Exception
	 */
	public function checkPermission()
	{
		if (empty($this->controller->headers['X-TOKEN'])) {
			throw new \Api\Core\Exception('Invalid token', 401);
		}
		$apiType = strtolower($this->controller->app['type']);
		$sessionTable = "w_#__{$apiType}_session";
		$userTable = "w_#__{$apiType}_user";
		$db = \App\Db::getInstance('webservice');
		$row = (new \App\Db\Query())->from($sessionTable)->innerJoin($userTable, "$sessionTable.user_id = $userTable.id")
				->where(["$sessionTable.id" => $this->controller->headers['X-TOKEN'], "$userTable.status" => 1])->one($db);
		if (empty($row)) {
			throw new \Api\Core\Exception('Invalid token', 401);
		}
		$this->session = new \App\Base();
		$this->session->setData($row);
		\App\User::setCurrentUserId($this->session->get('user_id'));
		$currentUser = (new \Users())->retrieveCurrentUserInfoFromFile($this->session->get('user_id'));
		vglobal('current_user', $currentUser);
		$db->createCommand()
			->update($sessionTable, ['changed' => date('Y-m-d H:i:s')], ['id' => $this->session->get('id')])
			->execute();
	}

	/**
	 * Pre process function
	 */
	public function preProcess()
	{
		$language = $this->getLanguage();
		if ($language) {
			\Vtiger_Language_Handler::$language = $language;
		}
	}

	/**
	 * Get current language
	 * @return string
	 */
	public function getLanguage()
	{
		$language = '';
		if (!empty($this->controller->headers['Accept-Language'])) {
			$language = $this->controller->headers['Accept-Language'];
		}
		if ($this->session && !$this->session->isEmpty('language')) {
			$language = $this->session->get('language');
		}
		return $language;
	}

	/**
	 * Get permission type
	 * @return int
	 */
	public function getPermissionType()
	{
		return $this->session->get('type');
	}

	/**
	 * Get crmid for portal user
	 * @return int
	 */
	public function getUserCrmId()
	{
		return $this->session->get('crmid');
	}

	/**
	 * Get parent record
	 * @return int
	 */
	public function getParentCrmId()
	{
		if ($this->controller) {
			if ($parentId = $this->controller->request->getHeader('X-PARENT-ID')) {
				settype($parentId, 'int');
				$hierarchy = new \Api\Portal\BaseModule\Hierarchy();
				$hierarchy->session = $this->session;
				$hierarchy->findId = $parentId;
				$hierarchy->moduleName = \App\Record::getType(\App\Record::getParentRecord($this->getUserCrmId()));
				$records = $hierarchy->get();
				if (isset($records[$parentId])) {
					return $parentId;
				} else {
					throw new \Api\Core\Exception('No permission to X-PARENT-ID', 403);
				}
			}
		}
		return \App\Record::getParentRecord($this->getUserCrmId());
	}
}
