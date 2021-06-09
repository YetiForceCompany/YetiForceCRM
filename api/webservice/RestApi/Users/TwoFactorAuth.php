<?php
/**
 * RestApi container - Users two factor authentication action file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\RestApi\Users;

use OpenApi\Annotations as OA;

/**
 * RestApi container - Users two factor authentication action class.
 */
class TwoFactorAuth extends \Api\Core\BaseAction
{
	use \Api\Core\Traits\LoginHistory;

	/** {@inheritdoc}  */
	public $allowedMethod = ['GET', 'POST', 'DELETE'];

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
	}

	/**
	 * Get user history of access activity.
	 *
	 * @return array
	 *
	 *	@OA\Get(
	 *		path="/webservice/RestApi/Users/TwoFactorAuth",
	 *		summary="Get two factor authentication details",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/Header-Encrypted")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Authentication secret details",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Get_TwoFactorAuth_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Get_TwoFactorAuth_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="Two-factor authentication has not been enabled",
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
	 *		schema="Users_Get_TwoFactorAuth_Response",
	 *		title="Users module - Authentication secret details",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success, 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *        	example=1
	 *		),
	 *		@OA\Property(
	 *     		property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object",
	 *   		@OA\Property(property="authMethods", type="string", example="TOTP"),
	 *   		@OA\Property(property="secretKey", type="string", example="LJUJWCOEGUKP6WS2"),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		$multiFactorAuth = new \Api\Core\TwoFactorAuth($this);
		if (!$multiFactorAuth->isActive()) {
			$this->saveLoginHistory([
				'status' => 'ERR_2FA_NOT_BEEN_ENABLED',
			]);
			$this->updateUser([
				'auth' => [
					'invalid_2fa' => 'Two-factor authentication has not been enabled',
					'invalid_2fa_time' => date('Y-m-d H:i:s'),
				],
			]);
			throw new \Api\Core\Exception('Two-factor authentication has not been enabled', 401);
		}
		if (empty($this->userData['auth']['authy_secret_key'])) {
			return $multiFactorAuth->generate();
		}
		return $multiFactorAuth->details();
	}

	/**
	 * Post method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/RestApi/Users/TwoFactorAuth",
	 *		summary="Activate two factor authentication",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 * 			@OA\Schema(ref="#/components/schemas/Header-Encrypted")
	 *      ),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Access details",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_post_TwoFactorAuth_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_post_TwoFactorAuth_Response")
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`Two-factor authentication has not been enabled` OR `A secret 2FA key has already been generated.`",
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
	 * 		schema="Users_post_TwoFactorAuth_Response",
	 * 		title="Users module - Activate two factor authentication",
	 * 		description="Activate two factor authentication response body",
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
	 *    	 	type="string",
	 *		),
	 *	),
	 */
	public function post(): string
	{
		$multiFactorAuth = new \Api\Core\TwoFactorAuth($this);
		if (!$multiFactorAuth->isActive()) {
			$this->saveLoginHistory([
				'status' => 'ERR_2FA_NOT_BEEN_ENABLED',
			]);
			$this->updateUser([
				'custom_params' => [
					'invalid_2fa' => 'Two-factor authentication has not been enabled',
					'invalid_2fa_time' => date('Y-m-d H:i:s'),
				],
			]);
			throw new \Api\Core\Exception('Two-factor authentication has not been enabled', 401);
		}
		if (!empty($this->userData['auth']['authy_secret_key'])) {
			$this->saveLoginHistory([
				'status' => 'ERR_2FA_ALREADY_GENERATED',
			]);
			$this->updateUser([
				'custom_params' => [
					'invalid_2fa' => 'A secret 2FA key has already been generated.',
					'invalid_2fa_time' => date('Y-m-d H:i:s'),
				],
			]);
			throw new \Api\Core\Exception('A secret 2FA key has already been generated.', 401);
		}
		return $multiFactorAuth->activate();
	}

	/**
	 * Delete record.
	 *
	 * @return bool
	 *
	 * @OA\Delete(
	 *		path="/webservice/RestApi/Users/TwoFactorAuth",
	 *		summary="Disable two factor authentication",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/Header-Encrypted")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Disable two factor authentication response",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Delete_TwoFactorAuth_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Delete_TwoFactorAuth_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="Two-factor authentication has not been enabled",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception")
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
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
		$multiFactorAuth = new \Api\Core\TwoFactorAuth($this);
		if (!$multiFactorAuth->isActive()) {
			$this->saveLoginHistory([
				'status' => 'ERR_2FA_NOT_BEEN_ENABLED',
			]);
			$this->updateUser([
				'custom_params' => [
					'invalid_2fa' => 'Two-factor authentication has not been enabled',
					'invalid_2fa_time' => date('Y-m-d H:i:s'),
				],
			]);
			throw new \Api\Core\Exception('Two-factor authentication has not been enabled', 401);
		}
		if ($check = $multiFactorAuth->check()) {
			throw new \Api\Core\Exception($check, 401);
		}
		$multiFactorAuth->delete();
		return true;
	}
}
