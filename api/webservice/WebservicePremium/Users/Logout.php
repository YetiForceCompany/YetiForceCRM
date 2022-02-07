<?php
/**
 * Webservice premium container - Users logout action file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\Users;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Users logout action class.
 */
class Logout extends \Api\WebserviceStandard\Users\Logout
{
	/**
	 * Put method.
	 *
	 * @return bool
	 *
	 * @OA\Put(
	 *		path="/webservice/WebservicePremium/Users/Logout",
	 *		description="Logout user out the system",
	 *		summary="Logout user",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="User details",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Put_Logout_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Put_Logout_Response"),
	 *		),
	 * ),
	 *	@OA\SecurityScheme(
	 * 		name="X-TOKEN",
	 *   	type="apiKey",
	 *   	in="header",
	 *		securityScheme="token",
	 *   	description="Webservice api token by user header"
	 *	),
	 * @OA\Schema(
	 * 		schema="Users_Put_Logout_Response",
	 * 		title="Users module - Users logout response body",
	 * 		description="JSON data",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Content of responses from a given method",
	 *			type="boolean",
	 *		),
	 * ),
	 */
	public function put(): bool
	{
		return parent::put();
	}
}
