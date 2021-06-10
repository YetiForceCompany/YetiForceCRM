<?php
/**
 * Portal container - User password reset action file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\Users;

/**
 * Portal container - User password reset action class.
 */
class ResetPassword extends \Api\RestApi\Users\ResetPassword
{
	/**
	 * Post method.
	 *
	 * @return bool
	 *
	 * @OA\Post(
	 *		path="/webservice/Portal/Users/ResetPassword",
	 *		description="User password reset",
	 *		summary="User password reset",
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
	 *		@OA\Parameter(
	 * 			name="X-ENCRYPTED",
	 * 			in="header",
	 * 			required=true,
	 * 			@OA\Schema(ref="#/components/schemas/Header-Encrypted")
	 *		),
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
	 * ),
	 *	@OA\Schema(
	 * 		schema="Users_Post_ResetPassword_Request",
	 * 		title="Users module - Users password reset request body",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="userName",
	 *			description="User name / email",
	 *			type="string",
	 * 		),
	 *	),
	 * @OA\Schema(
	 * 		schema="Users_Post_ResetPassword_Response",
	 * 		title="Users module - Users password reset response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *			example=1,
	 *		),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object",
	 *    		@OA\Property(property="expirationDate", type="string", example="2019-10-07 08:32:38"),
	 *    		@OA\Property(property="mailerStatus", type="boolean", example=true),
	 *		),
	 * ),
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
	 *		path="/webservice/Portal/Users/ResetPassword",
	 *		description="User password reset",
	 *		summary="User password reset",
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
	 *		@OA\Parameter(
	 * 			name="X-ENCRYPTED",
	 * 			in="header",
	 * 			required=true,
	 * 			@OA\Schema(ref="#/components/schemas/Header-Encrypted")
	 *		),
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
	 *  	@OA\Property(
	 *       	property="token",
	 *			description="A one-time password reset token",
	 *			type="string",
	 * 		),
	 *		@OA\Property(
	 *			property="password",
	 *			description="New password",
	 *			type="string"
	 *		),
	 *	),
	 *	@OA\Schema(
	 * 		schema="Users_Put_ResetPassword_Response",
	 * 		title="Users module - Users password reset response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *			example=1,
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Password reset status",
	 *			type="boolean",
	 *			example=false,
	 *		),
	 *	),
	 */
	public function put(): bool
	{
		return parent::put();
	}
}
