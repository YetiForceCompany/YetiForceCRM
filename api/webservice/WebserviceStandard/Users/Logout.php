<?php
/**
 * Webservice standard container - Users logout action file.
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
 * Webservice standard container - Users logout action class.
 */
class Logout extends \Api\Core\BaseAction
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
	 * @api
	 *
	 * @return bool
	 *
	 * @OA\Put(
	 *		path="/webservice/WebserviceStandard/Users/Logout",
	 *		description="Logout user out the system",
	 *		summary="Logout user",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="User details",
	 *			@OA\JsonContent(ref="#/components/schemas/UsersLogoutResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/UsersLogoutResponseBody"),
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
	 * 		schema="UsersLogoutResponseBody",
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
		\App\Db::getInstance('webservice')->createCommand()
			->delete($this->controller->app['tables']['session'], [
				'id' => $this->controller->headers['x-token'],
			])->execute();
		$this->saveLoginHistory([
			'status' => 'LBL_SIGNED_OFF',
		]);
		$this->updateUser([
			'custom_params' => [
				'logout_time' => date('Y-m-d H:i:s'),
			],
		]);
		return true;
	}
}
