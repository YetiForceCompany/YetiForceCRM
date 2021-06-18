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
	 *		description="Gets a list of records",
	 *		summary="List of records",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 1000", required=false, example=1000),
	 *		@OA\Parameter(name="x-row-offset", in="header", @OA\Schema(type="integer"), description="Offset, default: 0", required=false, example=0),
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
	 *			@OA\JsonContent(ref="#/components/schemas/Conditions-Mix-For-Query-Generator"),
	 *		),
	 *		@OA\Parameter(
	 *			name="x-only-column",
	 *			description="Return only column names",
	 *			@OA\Schema(type="integer", enum={0, 1}),
	 *			in="header",
	 *			example=1,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-parent-id",
	 *			description="Parent record id",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=5,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-cv-id",
	 *			description="Custom view ID",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=5,
	 *			required=false
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of entries",
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
	 *			description="`No permissions for module` OR `No permissions for custom view: x-cv-id`",
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
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, title="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
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
	 * 			@OA\Property(property="numberOfRecords", type="integer", description="Number of records on the page", example=54),
	 * 			@OA\Property(property="isMorePages", type="boolean", description="There are more pages", example=true),
	 * 			@OA\Property(property="numberOfAllRecords", type="integer", description="Number of all records, dependent on the header `x-row-count`", example=54),
	 * 		),
	 *	),
	 */
	public function get(): array
	{
		return parent::get();
	}
}
