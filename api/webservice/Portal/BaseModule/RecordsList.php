<?php
/**
 * Portal container - Get record list file.
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
 * Portal container - Get record list class.
 */
class RecordsList extends \Api\RestApi\BaseModule\RecordsList
{
	/**
	 * Get record list method.
	 *
	 * @return array
	 *
	 * @OA\GET(
	 *		path="/webservice/Portal/{moduleName}/RecordsList",
	 *		summary="Get the list of records",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="The content of the request is empty",
	 *		),
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="x-raw-data",
	 *			description="Get rows limit, default: 0",
	 *			@OA\Schema(type="integer", enum={0, 1}),
	 *			in="header",
	 *			example=1,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-limit",
	 *			description="Get rows limit, default: 1000",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=1000,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-offset",
	 *			description="Offset, default: 0",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=0,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-order-field",
	 *			description="Sets the ORDER BY part of the query record list",
	 *			@OA\Schema(type="string"),
	 *			in="header",
	 *			example="lastname",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-order",
	 *			description="Sorting direction",
	 *			@OA\Schema(type="string", enum={"ASC", "DESC"}),
	 *			in="header",
	 *			example="DESC",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-fields",
	 *			description="JSON array in the list of fields to be returned in response",
	 *			in="header",
	 *			required=false,
	 *			@OA\JsonContent(
	 *				type="array",
	 * 				@OA\Items(type="string"),
	 * 			)
	 *		),
	 *		@OA\Parameter(
	 *			name="x-condition",
	 * 			description="Conditions [Json format]",
	 *			in="header",
	 *			required=false,
	 *			@OA\JsonContent(
	 *				description="Conditions details",
	 *				type="object",
	 *				@OA\Property(property="fieldName", description="Field name", type="string", example="lastname"),
	 *				@OA\Property(property="value", description="Search value", type="string", example="Kowalski"),
	 *				@OA\Property(property="operator", description="Field operator", type="string", example="e"),
	 *				@OA\Property(property="group", description="Condition group if true is AND", type="boolean", example=true),
	 *			),
	 *		),
	 *		@OA\Parameter(
	 *			name="x-parent-id",
	 *			description="Parent record id",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=5,
	 *			required=false
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of consents",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_RecordsList_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_RecordsList_ResponseBody"),
	 *		),
	 *		@OA\Response(
	 *			response=400,
	 *			description="Incorrect json syntax: x-fields",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="No sent token, Invalid token, Token has expired",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions for module",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *),
	 * @OA\Schema(
	 *		schema="BaseModule_RecordsList_ResponseBody",
	 *		title="Base module - Response action record list",
	 *		description="Module action record list response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={"0", "1"},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="List of records",
	 *			type="object",
	 *			@OA\Property(
	 *				property="headers",
	 *				description="Column names",
	 *				type="object",
	 *				@OA\AdditionalProperties,
	 *			),
	 *			@OA\Property(
	 *				property="records",
	 *				description="Records display details",
	 *				type="object",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Display_Details"),
	 *			),
	 *			@OA\Property(
	 *				property="rawData",
	 *				description="Records raw details",
	 *				type="object",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Raw_Details"),
	 *			),
	 * 			@OA\Property(property="count", type="string", example=54),
	 * 			@OA\Property(property="isMorePages", type="boolean", example=true),
	 * 		),
	 *	),
	 */
	public function get(): array
	{
		return parent::get();
	}
}
