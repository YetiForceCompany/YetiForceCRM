<?php

/**
 * Webservice premium container - Get modules list action file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseAction;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Get modules list action class.
 */
class Modules extends \Api\WebserviceStandard\BaseAction\Modules
{
	/**
	 * Get permitted modules.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebservicePremium/Modules",
	 *		description="Get the permitted module list action, along with their translated action",
	 *		summary="The allowed actions of the module list",
	 *		tags={"BaseAction"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of permitted modules",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Get_Modules_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Get_Modules_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`No sent token` OR `Invalid token`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions for module",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseAction_Get_Modules_Response",
	 *		title="Base action - List of permitted modules",
	 *		description="List of available modules",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="List of permitted modules",
	 *			type="object",
	 *			@OA\AdditionalProperties(description="Module name", type="string", example="Accounts"),
	 * 		),
	 *	),
	 */
	public function get(): array
	{
		return parent::get();
	}
}
