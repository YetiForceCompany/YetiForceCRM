<?php
/**
 * Portal container - Get related modules file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Portal container - Get related modules class.
 */
class RelatedModules extends \Api\RestApi\BaseModule\RelatedModules
{
	/**
	 * Get related modules list method.
	 *
	 * @return array
	 *
	 *	@OA\Get(
	 *		path="/webservice/Portal/{moduleName}/RelatedModules/{recordId}",
	 *		description="Gets a list of related modules",
	 *		summary="Related list of modules",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Response(
	 *			response=200, description="List of related modules",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_RelatedModules_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_RelatedModules_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=403, description="`No permissions to view record`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404, description="`Record doesn't exist` OR `No record id`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Get_RelatedModules_Response",
	 *		title="Base module - Response action related modules list",
	 *		description="Module action related modules list response body",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="List of related records",
	 *			@OA\Property(property="base", type="object", title="Base list",
	 *				@OA\AdditionalProperties(type="object",
	 * 					@OA\Property(property="type", type="string", description="Type", example="Summary"),
	 * 					@OA\Property(property="label", type="string", description="Translated label", example="Summary"),
	 * 					@OA\Property(property="icon", type="string", description="Icon class", example="far fa-address-card"),
	 * 				),
	 *			),
	 *			@OA\Property(property="related", type="object", title="Base list",
	 *				@OA\AdditionalProperties(type="object",
	 * 					@OA\Property(property="relationId", type="integer", description="Relation ID", example=3),
	 * 					@OA\Property(property="relatedModuleName", type="string", description="Related module name", example="Documents"),
	 * 					@OA\Property(property="icon", type="string", description="Icon class", example="far fa-address-card"),
	 * 					@OA\Property(property="label", type="string", description="Translated label", example="Documents"),
	 * 				),
	 *			),
	 * 		),
	 *	),
	 */
	public function get(): array
	{
		return parent::get();
	}
}
