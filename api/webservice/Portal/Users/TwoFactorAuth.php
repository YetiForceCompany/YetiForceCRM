<?php
/**
 * Portal container - Users two-factor authentication action file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\Users;

use OpenApi\Annotations as OA;

/**
 * Portal container - Users two-factor authentication action class.
 */
class TwoFactorAuth extends \Api\RestApi\Users\TwoFactorAuth
{
	/**
	 * Post method.
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/Portal/Users/TwoFactorAuth",
	 *		description="Two Factor authentication",
	 *		summary="This API allows you to manage two factor authentication (2FA) for users. Auth methods: TOTP ",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data format",
	 *    		@OA\JsonContent(ref="#/components/schemas/Users_TwoFactorAuth_RequestBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/Users_TwoFactorAuth_RequestBody")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/Users_TwoFactorAuth_RequestBody")
	 *     		),
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 * 			@OA\Schema(ref="#/components/schemas/Header-Encrypted")
	 *      ),
	 *		@OA\Response(
	 *			response=200,
	 *			description="User access details",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_TwoFactorAuth_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_TwoFactorAuth_ResponseBody")
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`Two-factor authentication has not been enabled` OR `A secret 2FA key has already been generated.` OR `Invalid data access` OR `Invalid user password` OR `No crmid`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_TwoFactorAuth_RequestBody",
	 * 		title="Users module - Users login request body",
	 * 		description="JSON or form-data",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="userName",
	 *			description="Webservice user name",
	 *			type="string",
	 * 		),
	 *		@OA\Property(
	 *			property="password",
	 *			description="Webservice user password",
	 *			type="string"
	 *		),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_TwoFactorAuth_ResponseBody",
	 * 		title="Users module - Users login response body",
	 * 		description="Users login response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 * 			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 * 			enum={0, 1},
	 *     	  	type="integer",
	 * 			example=1
	 * 		),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object",
	 *   		@OA\Property(property="authMethods", type="string", example="TOTP"),
	 *   		@OA\Property(property="secretKey", type="string", example="LJUJWCOEGUKP6WS2"),
	 *		),
	 *	),
	 */
	public function post(): array
	{
		return parent::post();
	}
}
