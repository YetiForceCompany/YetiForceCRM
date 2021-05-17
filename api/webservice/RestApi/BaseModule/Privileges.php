<?php

/**
 * RestApi container - Get Privileges file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\RestApi\BaseModule;

/**
 * RestApi container - Get Privileges  class.
 */
class Privileges extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/**
	 * Get privileges for module.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/RestApi/{moduleName}/Privileges",
	 *		summary="Get privileges for module",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(
	 *				type="string"
	 *			),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="Request body does not occur",
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Privileges details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Privileges_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Privileges_ResponseBody"),
	 *		),
	 * ),
	 * @OA\Schema(
	 * 		schema="BaseModule_Privileges_ResponseBody",
	 * 		title="Base module - Privileges response schema",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 * 			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 * 			enum={0, 1},
	 *     	  	type="integer",
	 * 			example=1
	 * 		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="List of module privileges",
	 *			type="object",
	 *			example={"Import" : true, "Export" : true},
	 *			@OA\AdditionalProperties(description="Action", type="boolean"),
	 * 		),
	 * ),
	 */
	public function get(): array
	{
		$userId = $this->session->get('user_id');
		$privileges = [];
		if (\App\User::isExists($userId)) {
			$moduleId = \App\Module::getModuleId($this->controller->request->getModule('module'));
			$actionPermissions = \App\User::getPrivilegesFile($userId);
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
