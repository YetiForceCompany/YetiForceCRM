<?php
/**
 * Portal container - User password change action file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\Users;

use OpenApi\Annotations as OA;

/**
 * Portal container - User password change action class.
 */
class ChangePassword extends \Api\RestApi\Users\ChangePassword
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['PUT'];

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
	}

	/**
	 * Put method.
	 *
	 * @return bool
	 *
	 * @OA\Put(
	 *		path="/webservice/Portal/Users/ChangePassword",
	 *		description="User password change",
	 *		summary="User password change",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data",
	 *    		@OA\JsonContent(ref="#/components/schemas/Users_Put_ChangePassword_Request"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Put_ChangePassword_Request")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Put_ChangePassword_Request")
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
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Put_ChangePassword_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Put_ChangePassword_Response"),
	 *		),
	 * ),
	 *	@OA\Schema(
	 * 		schema="Users_Put_ChangePassword_Request",
	 * 		title="Users module - Users password change request body",
	 *		type="object",
	 *		required={"currentPassword", "newPassword"},
	 *  	@OA\Property(
	 *       	property="currentPassword",
	 *			title="Current password",
	 *			type="string",
	 * 		),
	 *		@OA\Property(
	 *			property="newPassword",
	 *			title="New password",
	 *			type="string"
	 *		),
	 *	),
	 * @OA\Schema(
	 * 		schema="Users_Put_ChangePassword_Response",
	 * 		title="Users module - Users password change response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(
	 *			property="status",
	 *			title="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *			example=1,
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Password change status",
	 *			type="boolean",
	 *			example=false,
	 *		),
	 * ),
	 */
	public function put(): bool
	{
		return parent::put();
	}
}
