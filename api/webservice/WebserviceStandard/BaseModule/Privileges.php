<?php

/**
 * Webservice standard container - Get Privileges file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebserviceStandard\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - Get Privileges  class.
 */
class Privileges extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/**
	 * Get privileges for module.
	 *
	 * @api
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebserviceStandard/{moduleName}/Privileges",
	 *		description="Gets the list of actions that the user has access to in the module",
	 *		summary="Privileges for module actions",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Privileges details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Privileges_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Privileges_ResponseBody"),
	 *		),
	 * ),
	 * @OA\Schema(
	 * 		schema="BaseModule_Privileges_ResponseBody",
	 * 		title="Base module - Privileges response schema",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", description="List of module privileges",
	 *			example={"EditView" : true, "Delete" : true, "DetailView" : true, "CreateView" : true},
	 *			@OA\AdditionalProperties(type="boolean", description="Action"),
	 * 		),
	 * ),
	 */
	public function get(): array
	{
		$privileges = [];
		if (\App\User::isExists($this->getUserData('user_id'))) {
			$moduleName = $this->controller->request->getModule('module');
			$moduleId = \App\Module::getModuleId($moduleName);
			$actionPermissions = \App\User::getPrivilegesFile($this->getUserData('user_id'));
			$isAdmin = $actionPermissions['is_admin'];
			$permission = $actionPermissions['profile_action_permission'][$moduleId] ?? false;
			if ($permission || $isAdmin) {
				$actions = array_merge(\Vtiger_Action_Model::getAllBasic(true), \Vtiger_Action_Model::getAllUtility(true));
				foreach ($actions as $action) {
					$privileges[$action->getName()] = $isAdmin || (isset($permission[$action->getId()]) && \Settings_Profiles_Module_Model::IS_PERMITTED_VALUE === $permission[$action->getId()]);
				}
			}
		}
		return $privileges;
	}
}
