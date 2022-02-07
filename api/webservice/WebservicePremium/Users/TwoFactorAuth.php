<?php
/**
 * Webservice premium container - Users two-factor authentication action file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\Users;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Users two-factor authentication action class.
 */
class TwoFactorAuth extends \Api\WebserviceStandard\Users\TwoFactorAuth
{
	/**
	 * Get two factor authentication details.
	 *
	 * @return array
	 *
	 *	@OA\Get(
	 *		path="/webservice/WebservicePremium/Users/TwoFactorAuth",
	 *		summary="2FA details",
	 *		description="Get two factor authentication details",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Authentication secret details",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Get_TwoFactorAuth_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Get_TwoFactorAuth_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=500,
	 *			description="Two-factor authentication has not been enabled",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="Users_Get_TwoFactorAuth_Response",
	 *		title="Users module - Authentication secret details",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	title="Content of responses from a given method",
	 *    	 	type="object",
	 *    	 	required={"authMethods", "secretKey"},
	 *   		@OA\Property(property="authMethods", type="string", example="TOTP"),
	 *   		@OA\Property(property="secretKey", type="string", example="LJUJWCOEGUKP6WS2"),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		return parent::get();
	}

	/**
	 * Post method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/WebservicePremium/Users/TwoFactorAuth",
	 *		summary="Activate 2FA",
	 *		description="Activate two factor authentication",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Access details",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_post_TwoFactorAuth_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_post_TwoFactorAuth_Response")
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=500,
	 *			description="`Two-factor authentication has not been enabled` OR `A secret 2FA key has already been generated.`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_post_TwoFactorAuth_Response",
	 * 		title="Users module - Activate two factor authentication",
	 * 		description="Activate two factor authentication response body",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="string",
	 *		),
	 *	),
	 */
	public function post(): string
	{
		return parent::post();
	}

	/**
	 * Delete record.
	 *
	 * @return bool
	 *
	 * @OA\Delete(
	 *		path="/webservice/WebservicePremium/Users/TwoFactorAuth",
	 *		summary="Disable 2FA",
	 *		description="Disable two factor authentication",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Disable two factor authentication response",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Delete_TwoFactorAuth_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Delete_TwoFactorAuth_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=500,
	 *			description="Two-factor authentication has not been enabled",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="Users_Delete_TwoFactorAuth_Response",
	 *		title="Users module - Disable two factor authentication",
	 *		description="Disable two factor authentication response",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success, 0 - error",
	 *			enum={0, 1},
	 * 			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Status of successful",
	 *			type="boolean",
	 *		),
	 * ),
	 */
	public function delete(): bool
	{
		return parent::delete();
	}
}
