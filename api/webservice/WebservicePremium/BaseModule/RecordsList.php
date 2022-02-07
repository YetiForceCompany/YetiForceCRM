<?php
/**
 * Webservice premium container - Get record list file.
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
 * Webservice premium container - Get record list class.
 */
class RecordsList extends \Api\WebserviceStandard\BaseModule\RecordsList
{
	/**
	 * Get method - Get record list method.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebservicePremium/{moduleName}/RecordsList",
	 *		summary="List of records",
	 *		description="Gets a list of records",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 100", required=false, example=50),
	 *		@OA\Parameter(name="x-row-offset", in="header", @OA\Schema(type="integer"), description="Offset, default: 0", required=false, example=0),
	 *		@OA\Parameter(name="x-fields", in="header", description="JSON array in the list of fields to be returned in response", required=false,
	 *			@OA\JsonContent(type="array", example={"field_name_1", "field_name_2"}, @OA\Items(type="string")),
	 *		),
	 *		@OA\Parameter(name="x-condition", in="header", description="Conditions [Json format]", required=false,
	 *			@OA\JsonContent(ref="#/components/schemas/Conditions-Mix-For-Query-Generator"),
	 *		),
	 *		@OA\Parameter(name="x-only-column", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Return only column names", required=false, example=1),
	 *		@OA\Parameter(name="x-parent-id", in="header", @OA\Schema(type="integer"), description="Parent record id", required=false, example=5),
	 *		@OA\Parameter(name="x-cv-id", in="header", @OA\Schema(type="integer"), description="Custom view ID", required=false, example=5),
	 *		@OA\Parameter(name="x-order-by", in="header", description="Set the sorted results by columns [Json format]", required=false,
	 * 			@OA\JsonContent(type="object", title="Sort conditions", description="Multiple or one condition for a query generator",
	 * 				example={"field_name_1" : "ASC", "field_name_2" : "DESC"},
	 * 				@OA\AdditionalProperties(type="string", title="Sort Direction", enum={"ASC", "DESC"}),
	 * 			),
	 *		),
	 *		@OA\Response(response=200, description="List of entries",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_RecordsList_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_RecordsList_Response"),
	 *		),
	 *		@OA\Response(response=400, description="Incorrect json syntax: x-fields",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=401, description="No sent token, Invalid token, Token has expired",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=403, description="`No permissions for module` OR `No permissions for custom view: x-cv-id`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=405, description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *),
	 * @OA\Schema(
	 *		schema="BaseModule_Get_RecordsList_Response",
	 *		title="Base module - Response action record list",
	 *		description="Module action record list response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="List of records",
	 *			required={"headers", "records", "permissions", "numberOfRecords", "isMorePages"},
	 *			@OA\Property(property="headers", type="object", title="Fields names", example={"field_name_1" : "Field label 1", "field_name_2" : "Field label 2", "assigned_user_id" : "Assigned user", "createdtime" : "Created time"},
	 * 				@OA\AdditionalProperties(type="string", description="Field name"),
	 *			),
	 *			@OA\Property(property="records", type="object", title="Records display details",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Display_Details"),
	 *			),
	 *			@OA\Property(property="permissions", type="object", title="Records action permissions",
	 *				@OA\AdditionalProperties(type="object", title="Record action permissions",
	 *					required={"isEditable", "moveToTrash"},
	 *					@OA\Property(property="isEditable", type="boolean", example=true),
	 *					@OA\Property(property="moveToTrash", type="boolean", example=true),
	 *				),
	 *			),
	 *			@OA\Property(property="rawData", type="object", title="Records raw details, dependent on the header `x-raw-data`",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Raw_Details"),
	 *			),
	 * 			@OA\Property(property="numberOfRecords", type="integer", description="Number of records on the page", example=20),
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
