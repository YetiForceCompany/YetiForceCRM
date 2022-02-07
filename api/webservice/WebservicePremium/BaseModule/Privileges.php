<?php

/**
 * Webservice premium container - Get Privileges file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Get Privileges  class.
 */
class Privileges extends \Api\WebserviceStandard\BaseModule\Privileges
{
	/**
	 * Get privileges for module.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebservicePremium/{moduleName}/Privileges",
	 *		description="Gets the list of actions that the user has access to in the module",
	 *		summary="Get privileges for module",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Privileges details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_Privileges_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_Privileges_Response"),
	 *		),
	 * ),
	 * @OA\Schema(
	 * 		schema="BaseModule_Get_Privileges_Response",
	 * 		title="Base module - Privileges response schema",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			title="List of module privileges",
	 *			type="object",
	 *			example={"Import" : true, "Export" : true},
	 *			@OA\AdditionalProperties(title="Action", type="boolean"),
	 * 		),
	 * ),
	 */
	public function get(): array
	{
		return parent::get();
	}
}
