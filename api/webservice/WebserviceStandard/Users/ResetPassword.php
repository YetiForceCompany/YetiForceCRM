<?php
/**
 * Webservice standard container - User password reset action file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebserviceStandard\Users;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - User password reset action class.
 */
class ResetPassword extends \Api\Core\BaseAction
{
	use \Api\Core\Traits\LoginHistory;

	/** {@inheritdoc}  */
	public $allowedMethod = ['POST', 'PUT'];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
	}

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
	}

	/**
	 * Post method.
	 *
	 * @api
	 *
	 * @return bool
	 *
	 *	@OA\Post(
	 *		path="/webservice/WebserviceStandard/Users/ResetPassword",
	 *		description="User password reset - Generating and sending a one-time token",
	 *		summary="User password reset-Generatingandsendingaone-timetoken",
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
	 *		required={"userName"},
	 *  	@OA\Property(property="userName", type="string", description="User name / email"),
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
		$db = \App\Db::getInstance('webservice');
		$userData = (new \App\Db\Query())->from($this->controller->app['tables']['user'])
			->where(['user_name' => $this->controller->request->get('userName'), 'status' => 1])
			->limit(1)->one($db);
		if (!$userData) {
			$this->saveLoginHistory([
				'status' => 'ERR_EMAIL_NOT_FOUND',
			]);
			throw new \Api\Core\Exception(\App\Language::translate('LBL_USER_MAIL_NOT_EXIST', 'Users'), 404);
		}
		$this->setAllUserData($userData);
		\App\User::setCurrentUserId($userData['user_id']);
		$id = (int) $userData['id'];
		$expirationDate = date('Y-m-d H:i:s', strtotime('+1 hour'));
		$token = \App\Utils\Tokens::generate('\Api\WebserviceStandard\Users\ResetPassword', [$id], $expirationDate);
		$status = \App\Mailer::sendFromTemplate([
			'template' => 'UsersResetPassword',
			'to' => $userData['user_name'],
			'siteUrl' => $this->controller->app['url'] ?: '',
			'url' => $this->controller->app['url'] ? ($this->controller->app['url'] . 'index.php?module=Users&view=LoginPassReset&mode=token&token=' . $token) : '',
			'expirationDate' => date('Y-m-d H:i:s (T P)', strtotime($expirationDate)),
			'token' => $token,
		]);
		if ($status) {
			$this->saveLoginHistory([
				'status' => 'LBL_RESET_PASSWORD_REQUEST',
			]);
		} else {
			$this->saveLoginHistory([
				'status' => 'ERR_RESET_PASSWORD_REQUEST_SMTP_ERROR',
			]);
		}
		return [
			'expirationDate' => date('Y-m-d H:i:s (T P)', strtotime($expirationDate)),
			'mailerStatus' => $status,
		];
	}

	/**
	 * Put method.
	 *
	 * @api
	 *
	 * @return bool
	 *
	 *	@OA\Put(
	 *		path="/webservice/WebserviceStandard/Users/ResetPassword",
	 *		description="User password reset - Password change",
	 *		summary="User password reset-Passwordchange",
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
	 *		required={"token", "password"},
	 *  	@OA\Property(property="token", type="string", description="A one-time password reset token"),
	 *  	@OA\Property(property="password", type="string", description="New password"),
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
		$token = $this->controller->request->getByType('token', \App\Purifier::ALNUM);
		$tokenData = \App\Utils\Tokens::get($token);
		if (empty($tokenData) || empty($tokenData['params'][0])) {
			throw new \App\Exceptions\Security('ERR_TOKEN_DOES_NOT_EXIST', 405);
		}
		$db = \App\Db::getInstance('webservice');
		$userData = (new \App\Db\Query())->from($this->controller->app['tables']['user'])->where(['id' => $tokenData['params'][0], 'status' => 1])
			->limit(1)->one($db);
		if (!$userData) {
			$this->saveLoginHistory([
				'status' => 'ERR_EMAIL_NOT_FOUND',
			]);
			throw new \App\Exceptions\Security(\App\Language::translate('LBL_USER_MAIL_NOT_EXIST', 'Users'), 404);
		}
		$this->setAllUserData($userData);
		$this->updateUser([
			'password' => \App\Encryption::createPasswordHash($this->controller->request->getRaw('password'), $this->controller->app['type']),
		]);
		$this->saveLoginHistory([
			'status' => 'LBL_RESET_PASSWORD_CHANGED',
		]);
		return true;
	}
}
