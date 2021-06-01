<?php
/**
 * Portal container - Get user history of access activity file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license	YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\Users;

/**
 * Portal container - Get user history of access activity class.
 */
class AccessActivityHistory extends \Api\RestApi\Users\AccessActivityHistory
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/**
	 * Get user history of access activity.
	 *
	 * @return array
	 *
	 *	@OA\Get(
	 *		path="/webservice/RestApi/Users/AccessActivityHistory",
	 *		description="Get user history of access activity",
	 *		summary="History of access activity data",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-limit",
	 *			description="Get rows limit, default: 50",
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
	 *			name="x-condition",
	 * 			description="Conditions [Json format]",
	 *			in="header",
	 *			required=false,
	 *			@OA\JsonContent(ref="#/components/schemas/Conditions-For-Native-Query")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="User history of access activity",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Get_AccessActivityHistory_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Get_AccessActivityHistory_Response"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="Users_Get_AccessActivityHistory_Response",
	 *		title="Users - History of access activity data",
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
	 *			description="User data",
	 *			type="object",
	 *			@OA\AdditionalProperties(
	 *				description="Condition details",
	 *				type="object",
	 *				@OA\Property(property="time", type="string", description="Date time in user format", example="2021-06-01 11:57"),
	 *				@OA\Property(property="status", type="string", description="Operation name", example="Signed in"),
	 *				@OA\Property(property="agent", type="string", description="User agent", example="PostmanRuntime/7.28.0"),
	 *				@OA\Property(property="ip", type="string", description="IP address", example="127.0.0.1"),
	 *			),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		return parent::get();
	}
}
