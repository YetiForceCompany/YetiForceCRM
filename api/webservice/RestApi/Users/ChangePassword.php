<?php
/**
 * RestApi container - User password change action file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\RestApi\Users;

use OpenApi\Annotations as OA;

/**
 * RestApi container - User password change action class.
 */
class ChangePassword extends \Api\Core\BaseAction
{
	use \Api\Core\Traits\LoginHistory;

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
	 *		path="/webservice/RestApi/Users/ChangePassword",
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
	 *  	@OA\Property(
	 *       	property="currentPassword",
	 *			description="Current password",
	 *			type="string",
	 * 		),
	 *		@OA\Property(
	 *			property="newPassword",
	 *			description="New password",
	 *			type="string"
	 *		),
	 *	),
	 * @OA\Schema(
	 * 		schema="Users_Put_ChangePassword_Response",
	 * 		title="Users module - Users password change response body",
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
	 *			description="Password change status",
	 *			type="boolean",
	 *			example=false,
	 *		),
	 * ),
	 */
	public function put(): bool
	{
		if (\App\Encryption::verifyPasswordHash($this->controller->request->getRaw('currentPassword'), $this->userData['password'], $this->controller->app['type'])) {
			$this->updateUser([
				'password' => \App\Encryption::createPasswordHash($this->controller->request->getRaw('currentPassword'), $this->controller->app['type']),
			]);
			$this->saveLoginHistory([
				'status' => 'LBL_PASSWORD_CHANGED',
			]);
			return true;
		}
		$this->saveLoginHistory([
			'status' => 'LBL_FAILED_PASSWORD_CHANGED',
		]);
		return false;
	}
}
