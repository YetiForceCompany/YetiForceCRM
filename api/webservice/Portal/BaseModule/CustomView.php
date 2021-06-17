<?php
/**
 * Portal container for custom view - file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Portal container for custom view - class.
 */
class CustomView extends \Api\RestApi\BaseModule\CustomView
{
	/**
	 * Get custom view list method.
	 *
	 * @return array
	 *
	 * @OA\GET(
	 *		path="/webservice/Portal/{moduleName}/CustomView",
	 *		description="Gets a list of custom view",
	 *		summary="List of custom view",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of custom view",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_CustomView_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_CustomView_ResponseBody"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`No sent token`, `Invalid token`, `Token has expired`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="`No permissions for module`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="`Invalid method`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\GET(
	 *		path="/webservice/Portal/{moduleName}/CustomView/{cvId}",
	 *		operationId="Api\Portal\BaseModule\CustomView::get(cvId)",
	 *		description="Gets data of custom view",
	 *		summary="Data of custom view",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 * 		@OA\Parameter(
	 *			name="cvId",
	 *			description="Custom view ID",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=12,
	 *			required=true
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Data of custom view",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_CustomViewById_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_CustomViewById_ResponseBody"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`No sent token`, `Invalid token`, `Token has expired`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="`No permissions for module or data provided in the request`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="`Invalid method`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_CustomView_ResponseBody",
	 *		title="Base module - Response action - data of custom view list",
	 *		description="Module action - Data of custom view list - response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="List of custom view",
	 *			type="object",
	 *			@OA\AdditionalProperties(type="object", ref="#/components/schemas/BaseModule_CustomViewById_Result"),
	 * 		),
	 *	),
	 * @OA\Schema(
	 *		schema="BaseModule_CustomViewById_ResponseBody",
	 *		title="Base module - Response action - data of custom view by specific ID",
	 *		description="Module action - custom view for specific ID - response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Data of custom view",
	 *			type="object",
	 *			ref="#/components/schemas/BaseModule_CustomViewById_Result",
	 * 		),
	 *	),
	 * @OA\Schema(
	 *		schema="BaseModule_CustomViewById_Result",
	 *		title="Data of custom view by specific ID",
	 *		description="Module action - custom view for specific ID - response data",
	 *		type="object",
	 *		@OA\Property(
	 *			property="cvid",
	 *			description="Custom view ID",
	 *			type="integer",
	 *			example=12
	 *		),
	 *		@OA\Property(
	 *			property="viewname",
	 *			description="Custom view name",
	 *			type="string",
	 *			example="Test 1"
	 *		),
	 *		@OA\Property(
	 *			property="entitytype",
	 *			description="Module name",
	 *			type="string",
	 *			example="Accounts"
	 *		),
	 *		@OA\Property(
	 *			property="sequence",
	 *			description="Sequence",
	 *			type="integer",
	 *			example=1
	 *		),
	 *		@OA\Property(
	 *			property="description",
	 *			description="Custom view description",
	 *			type="string",
	 *			example="Description"
	 *		),
	 *		@OA\Property(
	 *			property="color",
	 *			description="Color for custom view",
	 *			type="string",
	 *			example="#c28306"
	 *		),
	 *		@OA\Property(
	 *			property="isFeatured",
	 *			description="Custom view is in favorites",
	 *			type="boolean",
	 *			example=false
	 *		),
	 *		@OA\Property(
	 *			property="isDefault",
	 *			description="Custom view is default",
	 *			type="boolean",
	 *			example=false
	 *		),
	 *	),
	 */
	public function get(): array
	{
		return parent::get();
	}
}
