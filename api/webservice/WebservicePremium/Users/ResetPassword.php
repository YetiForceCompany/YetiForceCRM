<?php
/**
 * Webservice premium container - User password reset action file.
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
 * Webservice premium container - User password reset action class.
 */
class ResetPassword extends \Api\WebserviceStandard\Users\ResetPassword
{
	/**
	 * Post method.
	 *
	 * @return bool
	 *
	 * @OA\Post(
	 *		path="/webservice/WebservicePremium/Users/ResetPassword",
	 *		description="User password reset - Generating and sending a one-time token",
	 *		summary="User password reset - Generating and sending a one-time token",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data",
	 *    		@OA\JsonContent(ref="#/components/schemas/Users_Post_ResetPassword_Request"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Post_ResetPassword_Request")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Post_ResetPassword_Request")
	 *     		),
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Response",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Post_ResetPassword_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Post_ResetPassword_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="User with this email address does not exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Post_ResetPassword_Request",
	 * 		title="Users module - Users password reset request body",
	 *		type="object",
	 *		required={"userName", "deviceId"},
	 *  	@OA\Property(property="userName", type="string", description="User name / email"),
	 * 		@OA\Property(property="deviceId", type="string", description="Portal user device ID", example="d520c7a8-421b-4563-b955-f5abc56b97ec"),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Post_ResetPassword_Response",
	 * 		title="Users module - Users password reset response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	title="Content of responses from a given method",
	 *    	 	type="object",
	 *    		@OA\Property(property="expirationDate", type="string", example="2019-10-07 08:32:38"),
	 *    		@OA\Property(property="mailerStatus", type="boolean", example=true),
	 *		),
	 *	),
	 */
	public function post(): array
	{
		return parent::post();
	}

	/**
	 * Put method.
	 *
	 * @return bool
	 *
	 *	@OA\Put(
	 *		path="/webservice/WebservicePremium/Users/ResetPassword",
	 *		description="User password reset - Password change",
	 *		summary="User password reset - Password change",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data",
	 *    		@OA\JsonContent(ref="#/components/schemas/Users_Put_ResetPassword_Request"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Put_ResetPassword_Request")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Put_ResetPassword_Request")
	 *     		),
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Response",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Put_ResetPassword_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Put_ResetPassword_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="User with this email address does not exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="ERR_TOKEN_DOES_NOT_EXIST",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Put_ResetPassword_Request",
	 * 		title="Users module - Users password reset request body",
	 *		type="object",
	 *		required={"token", "password", "deviceId"},
	 *  	@OA\Property(property="token", type="string", description="A one-time password reset token"),
	 *  	@OA\Property(property="password", type="string", description="New password"),
	 *		@OA\Property(property="deviceId", type="string", description="Portal user device ID", example="d520c7a8-421b-4563-b955-f5abc56b97ec"),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Put_ResetPassword_Response",
	 * 		title="Users module - Users password reset response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Password reset status",
	 *			type="boolean",
	 *			example=false,
	 *		),
	 *	),
	 */
	public function put(): bool
	{
		return parent::put();
	}

	/** {@inheritdoc}  */
	protected function saveLoginHistory(array $data): void
	{
		parent::saveLoginHistory(array_merge($data, [
			'device_id' => $this->controller->request->getByType('deviceId', \App\Purifier::ALNUM_EXTENDED),
		]));
	}
}
