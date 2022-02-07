<?php
/**
 * Webservice premium container - Get related modules file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Get related modules class.
 */
class RelatedModules extends \Api\WebserviceStandard\BaseModule\RelatedModules
{
	/**
	 * Get related modules list method.
	 *
	 * @return array
	 *
	 *	@OA\Get(
	 *		path="/webservice/WebservicePremium/{moduleName}/RelatedModules",
	 *		description="Gets a list of related modules",
	 *		summary="Related list of modules",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Response(response=200, description="List of related modules",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_RelatedModules_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_RelatedModules_Response"),
	 *		),
	 *		@OA\Response(response=401, description="`No sent token` OR `Invalid token`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=403, description="No permissions for module",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=405, description="Method Not Allowed",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Get_RelatedModules_Response",
	 *		title="Base module - Response action related modules list",
	 *		description="Module action related modules list response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="List of related records",
	 *			required={"base", "related"},
	 *			@OA\Property(property="base", type="object", title="Base list",
	 *				@OA\AdditionalProperties(type="object",
	 *					required={"type", "label", "icon"},
	 * 					@OA\Property(property="type", type="string", description="Type", example="Summary"),
	 * 					@OA\Property(property="label", type="string", description="Translated label", example="Summary"),
	 * 					@OA\Property(property="icon", type="string", description="Icon class", example="fas fa-desktop"),
	 * 				),
	 *			),
	 *			@OA\Property(property="related", type="object", title="Base list",
	 *				@OA\AdditionalProperties(type="object",
	 *					required={"label", "relationId", "relatedModuleName", "icon", "actions", "viewType", "customView"},
	 * 					@OA\Property(property="label", type="string", description="Translated label", example="Documents"),
	 * 					@OA\Property(property="relationId", type="integer", description="Relation ID", example=3),
	 * 					@OA\Property(property="relatedModuleName", type="string", description="Related module name", example="Documents"),
	 * 					@OA\Property(property="icon", type="string", description="Icon class", example="yfm-Documents"),
	 * 					@OA\Property(property="actions", type="array", @OA\Items(type="string"), description="Actions", example={"add", "select"}),
	 * 					@OA\Property(property="viewType", type="array", @OA\Items(type="string"), description="View types", example={"RelatedTab", "DetailBottom"}),
	 * 					@OA\Property(property="customView", type="array", @OA\Items(type="string"), description="Custom view", example={"relation", "all"}),
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
