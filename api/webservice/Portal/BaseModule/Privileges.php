<?php
namespace Api\Portal\BaseModule;

/**
 * Get Privileges class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Privileges extends \Api\Core\BaseAction
{

	/** @var string[] Request methods */
	protected $requestMethod = ['GET'];

	/**
	 * Get method
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$moduleId = \App\Module::getModuleId($moduleName);
		$actionPermissions = \App\User::getPrivilegesFile($this->session->get('user_id'));
		$permission = isset($actionPermissions['profile_action_permission'][$moduleId]) ? $actionPermissions['profile_action_permission'][$moduleId] : false;
		$privileges = [];
		if ($permission) {
			foreach (\Vtiger_Action_Model::$standardActions as $key => $value) {
				$privileges[$value] = isset($permission[$key]) && $permission[$key] === \Settings_Profiles_Module_Model::IS_PERMITTED_VALUE;
			}
		}
		return ['standardActions' => $privileges];
	}
}
