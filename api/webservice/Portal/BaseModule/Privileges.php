<?php

namespace Api\Portal\BaseModule;

/**
 * Get Privileges class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Privileges extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get method.
	 *
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$userId = $this->session->get('user_id');
		$privileges = [];
		if (\App\User::isExists($userId)) {
			$moduleId = \App\Module::getModuleId($moduleName);
			$actionPermissions = \App\User::getPrivilegesFile($userId);
			$isAdmin = $actionPermissions['is_admin'];
			$permission = $actionPermissions['profile_action_permission'][$moduleId] ?? false;
			if ($permission || $isAdmin) {
				foreach (\Vtiger_Action_Model::$standardActions as $key => $value) {
					$privileges[$value] = $isAdmin ||
						(isset($permission[$key]) && $permission[$key] === \Settings_Profiles_Module_Model::IS_PERMITTED_VALUE);
				}
			}
		}
		return ['standardActions' => $privileges];
	}
}
