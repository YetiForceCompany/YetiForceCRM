<?php
/**
 * Webservice premium container - Get user record detail file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license	YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\Users;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Get user record detail class.
 */
class Record extends \Api\WebserviceStandard\Users\Record
{
	/**
	 * Get user detail.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebservicePremium/Users/Record/{userId}",
	 *		description="Gets details about the user",
	 *		summary="Data for the user",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(
	 *			name="userId",
	 *			description="User id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Gets data for the user",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Get_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Get_Record_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="Access denied, access for administrators only",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="User doesn't exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="Users_Get_Record_Response",
	 *		title="Users module - Response body for user",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="User data",
	 *			type="object",
	 *			@OA\Property(property="name", description="User label", type="string", example="System Admin"),
	 *			@OA\Property(property="id", description="User Id", type="integer", example=1),
	 *			@OA\Property(property="fields", type="object", title="System field names and field labels", example={"field_name_1" : "Field label 1", "field_name_2" : "Field label 2"},
	 * 				@OA\AdditionalProperties(type="string", description="Field label"),
	 *			),
	 *			@OA\Property(
	 *				property="data",
	 *				description="User data",
	 *				type="object",
	 *			),
	 *			@OA\Property(
	 *				property="privileges",
	 *				description="Parameters determining checking of editing rights and moving to the trash",
	 * 				type="object",
	 *				@OA\Property(property="isEditable", description="Check if user is editable", type="boolean", example=true),
	 *				@OA\Property(property="moveToTrash", description="Permission to delete", type="boolean", example=false),
	 *			),
	 *			@OA\Property(property="rawData", description="Raw user data", type="object"),
	 *		),
	 * ),
	 */
	public function get(): array
	{
		return parent::get();
	}
}
