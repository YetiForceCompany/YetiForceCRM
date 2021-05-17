<?php
/**
 * Portal container - Get record history file.
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
 * Portal container - Get record history class.
 */
class RecordHistory extends \Api\Core\BaseAction
{
	/**
	 * Get related record list method.
	 *
	 * @return array
	 * @OA\Get(
	 *		path="/webservice/Portal/{moduleName}/RecordHistory/{recordId}",
	 *		description="Gets the history of the record",
	 *		summary="Record history",
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
	 *			name="x-start-with",
	 *			description="Show history from given ID",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=5972,
	 *			required=false
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
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Recent activities detail",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_RecordHistory_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_RecordHistory_ResponseBody"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions to view record OR MadTracker is turned off",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="Record doesn't exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_RecordHistory_ResponseBody",
	 *		title="Base module - Response action history record",
	 *		description="Action module for recent activities in CRM",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *			example=1
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Returns recent activities that took place in CRM",
	 *			type="object",
	 *			@OA\Property(
	 *				property="response",
	 *				description="Contains what actions have been performed and returns the data that has changed",
	 *				type="object",
	 *				@OA\AdditionalProperties(
	 *					type="object",
	 *					description="Key indicating the number of changes made to a given record",
	 * 					@OA\Property(property="time", type="string", description="Showing the exact date on which the change took place",  format="date-time", example="2019-10-07 08:32:38"),
	 *					@OA\Property(property="owner", type="string", description="Username of the user who made the change", example="System Admin"),
	 *					@OA\Property(property="status", type="string", description="Name of the action that was carried out", example="changed"),
	 * 					@OA\Property(property="rawTime", type="string", description="Showing the exact date on which the change took place",  format="date-time", example="2019-10-07 08:32:38"),
	 *					@OA\Property(property="rawOwner", type="integer", description="User ID of the user who made the change", example=1),
	 *					@OA\Property(property="rawStatus", type="string", description="The name of the untranslated label", example="LBL_UPDATED"),
	 *					@OA\Property(
	 *						property="data",
	 *						type="object",
	 *						description="Field system name",
	 *						@OA\AdditionalProperties(
	 *							@OA\Property(property="from", type="string", description="Value before change, dynamically collected value - the data type depends on the field type", example="Jan Kowalski"),
	 *							@OA\Property(property="to", type="string", description="Value after change, dynamically collected value - the data type depends on the field type", example="Jan Nowak"),
	 *							@OA\Property(property="rawFrom", type="string", description="Value before change", example="Jan Kowalski"),
	 *							@OA\Property(property="rawTo", type="string", description="Value after change", example="Jan Nowak"),
	 *							@OA\Property(property="targetModule", type="string", description="The name of the target related module", example="Contacts"),
	 *							@OA\Property(property="targetLabel", type="string", description="The label name of the target related module", example="Jan Kowalski"),
	 *							@OA\Property(property="targetId", type="integer", description="Id of the target related module", example=394),
	 *						),
	 *					),
	 *				),
	 *			),
	 *		),
	 *	),
	 */
	public function get()
	{
		return parent::get();
	}
}
