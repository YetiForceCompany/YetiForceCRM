<?php
/**
 * Portal container - Get record detail file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license	YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Portal container - Get record detail class.
 */
class Record extends \Api\RestApi\BaseModule\Record
{
	/**
	 * Get record detail.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/Portal/{moduleName}/Record/{recordId}",
	 *		description="Gets the details of a record",
	 *		summary="Data for the record",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		operationId="getRecord",
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="recordId",
	 *			description="Record id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Parameter(
	 *			name="x-raw-data",
	 *			description="Gets raw data",
	 *			@OA\Schema(type="integer", enum={0, 1}),
	 *			in="header",
	 *			example=1,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-parent-id",
	 *			description="Gets parent id",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			required=false
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Gets data for the record",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_Record_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="`No permissions to remove record` OR `No permissions to view record` OR `No permissions to edit record`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="`No record id` OR `Record doesn't exist`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Get_Record_Response",
	 *		title="Base module - Response body for Record",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *        	example=1
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Record data",
	 *			type="object",
	 *			@OA\Property(property="name", description="Record name", type="string", example="Driving school"),
	 *			@OA\Property(property="id", description="Record Id", type="integer", example=152),
	 *			@OA\Property(
	 * 				property="fields",
	 *				description="System field names and field labels",
	 *				type="object",
	 *				@OA\AdditionalProperties(description="Field label", type="boolean", example="Account name"),
	 *			),
	 *			@OA\Property(
	 *				property="data",
	 *				description="Record data",
	 *				type="object",
	 *				ref="#/components/schemas/Record_Display_Details",
	 *			),
	 *			@OA\Property(
	 *				property="privileges",
	 *				description="Parameters determining checking of editing rights and moving to the trash",
	 * 				type="object",
	 *				@OA\Property(property="isEditable", description="Check if record is editable", type="boolean", example=true),
	 *				@OA\Property(property="moveToTrash", description="Permission to delete", type="boolean", example=false),
	 *			),
	 *			@OA\Property(
	 *				property="inventory",
	 *				description="Value inventory data",
	 * 				type="object",
	 *			),
	 *			@OA\Property(
	 *				property="summaryInventory",
	 *				description="Value summary inventory data",
	 * 				type="object",
	 *			),
	 *			@OA\Property(property="rawData", description="Raw record data", type="object", ref="#/components/schemas/Record_Raw_Details"),
	 *			@OA\Property(property="rawInventory", description="Inventory data", type="object"),
	 *		),
	 * ),
	 * @OA\Tag(
	 *		name="BaseModule",
	 *		description="Access to record methods"
	 * )
	 */
	public function get(): array
	{
		return parent::get();
	}

	/**
	 * Delete record.
	 *
	 * @return bool
	 *
	 * @OA\Delete(
	 *		path="/webservice/Portal/{moduleName}/Record/{recordId}",
	 *		description="Changes the state of a record, moving it to the trash",
	 *		summary="Delete record",
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
	 *		@OA\Parameter(
	 *			name="recordId",
	 *			description="Record id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of records moved to the trash",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Delete_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Delete_Record_Response"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Delete_Record_Response",
	 *		title="Base module - Transfer to the trash",
	 *		description="List of records moved to the trash",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 * 			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Status of successful transfer of the record to the recycle bin",
	 *			type="boolean",
	 *		),
	 * ),
	 */
	public function delete(): bool
	{
		return parent::delete();
	}

	/**
	 * Edit record.
	 *
	 * @return array
	 *
	 * @OA\Put(
	 *		path="/webservice/Portal/{moduleName}/Record/{recordId}",
	 *		description="Retrieves data for editing a record",
	 *		summary="Edit record",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data record.",
	 *			@OA\JsonContent(ref="#/components/schemas/Record_Edit_Details"),
	 *			@OA\XmlContent(ref="#/components/schemas/Record_Edit_Details"),
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
	 *			name="recordId",
	 *			description="Record id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 * 			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Contents of the response contains only id",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Put_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Put_Record_Response"),
	 *			@OA\Link(link="GetRecordById", ref="#/components/links/GetRecordById")
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Put_Record_Response",
	 *		title="Base module - Response body for Record",
	 *		description="Contents of the response contains only id",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Gets data for the record",
	 *			description="Updated record id.",
	 *			type="object",
	 *			@OA\Property(property="id", description="Id of the newly created record", type="integer", example=22),
	 *			@OA\Property(property="skippedData", description="List of parameters passed in the request that were skipped in the write process", type="object"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="Record_Edit_Details",
	 *		title="Record edit details",
	 *		description="Record data in user format for edit view",
	 *		type="object",
	 *		example={"field_name_1" : "Tom", "field_name_2" : "Kowalski", "assigned_user_id" : 1, "createdtime" : "2014-09-24 20:51:12"},
	 * ),
	 * @OA\Schema(
	 *		schema="Record_Raw_Details",
	 *		title="Record raw details",
	 *		description="Record data in the system format as stored in a database",
	 *		type="object",
	 *		example={"id" : 11, "field_name_1" : "Tom", "field_name_2" : "Kowalski", "assigned_user_id" : 1, "createdtime" : "2014-09-24 20:51:12"},
	 * ),
	 * @OA\Schema(
	 *		schema="Record_Display_Details",
	 *		title="Record display details",
	 *		description="Record data in user format for preview",
	 *		type="object",
	 *		example={"id" : 11, "field_name_1" : "Tom", "field_name_2" : "Kowalski", "assigned_user_id" : "YetiForce Administrator", "createdtime" : "2014-09-24 20:51"},
	 * ),
	 */
	public function put(): array
	{
		return parent::put();
	}

	/**
	 * Create record.
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/Portal/{moduleName}/Record",
	 *		description="Gets data to save record",
	 *		summary="Create record",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data record.",
	 *			@OA\JsonContent(ref="#/components/schemas/Record_Edit_Details"),
	 *			@OA\XmlContent(ref="#/components/schemas/Record_Edit_Details"),
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
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Contents of the response contains only id",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Post_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Post_Record_Response"),
	 *			@OA\Link(link="GetRecordById", ref="#/components/links/GetRecordById")
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Post_Record_Response",
	 *		title="Base module - Created records",
	 *		description="Contents of the response contains only id",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Gets data for the record",
	 *			description="Created record id.",
	 *			type="object",
	 *			@OA\Property(property="id", description="Id of the newly created record", type="integer", example=22),
	 *			@OA\Property(property="skippedData", description="List of parameters passed in the request that were skipped in the write process", type="object"),
	 *		),
	 * ),
	 *	@OA\Link(link="GetRecordById",
	 *		description="The `id` value returned in the response can be used as the `recordId` parameter in `GET /webservice/{moduleName}/Record/{recordId}`.",
	 *		operationId="getRecord",
	 *		parameters={
	 *			"recordId" = "$response.body#/result/id"
	 *		}
	 *	)
	 */
	public function post(): array
	{
		return parent::post();
	}
}
