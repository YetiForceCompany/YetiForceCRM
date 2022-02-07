<?php
/**
 * Webservice premium container - Get the record history file.
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
 * Webservice premium container - Get the record history class.
 */
class RecordHistory extends \Api\WebserviceStandard\BaseModule\RecordHistory
{
	/**
	 * Get the record history.
	 *
	 * @return array
	 * @OA\Get(
	 *		path="/webservice/WebservicePremium/{moduleName}/RecordHistory/{recordId}",
	 *		description="Gets the history of the record",
	 *		summary="Record history",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 100", required=false, example=50),
	 *		@OA\Parameter(name="x-page", in="header", @OA\Schema(type="integer"), description="Page number, default: 1", required=false, example=1),
	 *		@OA\Parameter(name="x-start-with", in="header", @OA\Schema(type="integer"), description="Show history from given ID", required=false, example=5972),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Recent activities detail",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_RecordHistory_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_RecordHistory_Response"),
	 *		),
	 *		@OA\Response(response=403, description="`No permissions to view record` OR `MadTracker is turned off`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=404, description="Record doesn't exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Get_RecordHistory_Response",
	 *		title="Base module - Response action history record",
	 *		description="Action module for recent activities in CRM",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="Returns recent activities that took place in CRM",
	 *			required={"records", "isMorePages"},
	 * 			@OA\Property(property="records", type="object", title="Entires of recent record activities",
	 *				@OA\AdditionalProperties(type="object", title="Key indicating the number of changes made to a given record",
	 *					required={"time", "owner", "status"},
	 * 					@OA\Property(property="time", type="string", description="Showing the exact date on which the change took place", example="2019-10-07 08:32:38"),
	 *					@OA\Property(property="owner", type="string", description="Username of the user who made the change", example="System Admin"),
	 *					@OA\Property(property="status", type="string", description="Name of the action that was carried out", example="changed"),
	 * 					@OA\Property(property="rawTime", type="string", description="Showing the exact date on which the change took place",  example="2019-10-07 08:32:38"),
	 *					@OA\Property(property="rawOwner", type="integer", description="User ID of the user who made the change", example=1),
	 *					@OA\Property(property="rawStatus", type="string", description="The name of the untranslated label", example="LBL_UPDATED"),
	 *					@OA\Property(property="data", title="Additional information",
	 *						oneOf={
	 *							@OA\Schema(type="object", title="Record data create",
	 *								@OA\AdditionalProperties(
	 *									required={"label", "value", "raw"},
	 *									@OA\Property(property="label", type="string", description="Translated field label", example="Name"),
	 *									@OA\Property(property="value", type="string", description="Value, the data type depends on the field type", example="Jan Kowalski"),
	 *									@OA\Property(property="raw", type="string", description="Value in database format, only available in `x-raw-data`", example="Jan Kowalski"),
	 *								),
	 *							),
	 *							@OA\Schema(type="object", title="Record data change", description="Edit, conversation",
	 *								@OA\AdditionalProperties(
	 *									required={"label", "from", "to"},
	 *									@OA\Property(property="label", type="string", description="Translated field label", example="Name"),
	 *									@OA\Property(property="from", type="string", description="Value before change, the data type depends on the field type", example="Jan Kowalski"),
	 *									@OA\Property(property="to", type="string", description="Value after change, the data type depends on the field type", example="Jan Nowak"),
	 *									@OA\Property(property="rawFrom", type="string", description="Value before change, value in database format, only available in `x-raw-data`", example="Jan Kowalski"),
	 *									@OA\Property(property="rawTo", type="string", description="Value after change, value in database format, only available in `x-raw-data`", example="Jan Nowak"),
	 *								),
	 *							),
	 *							@OA\Schema(type="object", title="Operations on related records", description="Adding relations, removing relations, transferring records",
	 *								required={"targetModule", "targetModuleLabel", "targetLabel"},
	 *								@OA\Property(property="targetModule", type="string", description="The name of the target related module", example="Contacts"),
	 *								@OA\Property(property="targetModuleLabel", type="string", description="Translated module name", example="Kontakt"),
	 *								@OA\Property(property="targetLabel", type="string", description="The label name of the target related module", example="Jan Kowalski"),
	 *								@OA\Property(property="targetId", type="integer", description="Id of the target related module", example=394),
	 *							),
	 *						},
	 *					),
	 *				),
	 *			),
	 * 			@OA\Property(property="isMorePages", type="boolean", example=true),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		return parent::get();
	}
}
