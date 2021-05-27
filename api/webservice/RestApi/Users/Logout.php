<?php
/**
 * RestApi container - Users logout action file.
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
 * RestApi container - Users logout action class.
 */
class Logout extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['PUT'];

	/** {@inheritdoc}  */
	public function checkPermissionToModule(): void
	{
	}

	/**
	 * Put method.
	 *
	 * @return bool
	 *
	 * @OA\Put(
	 *		path="/webservice/RestApi/Users/Logout",
	 *		description="Logout user out the system",
	 *		summary="Logout user",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 * 			name="X-ENCRYPTED",
	 * 			in="header",
	 * 			required=true,
	 * 			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
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
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *			example=1
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Content of responses from a given method",
	 *			type="boolean",
	 *		),
	 * ),
	 */
	public function put(): bool
	{
		$db = \App\Db::getInstance('webservice');
		$db->createCommand()->delete(\Api\Core\Containers::$listTables[$this->controller->app['type']]['session'], [
			'id' => $this->controller->headers['x-token'],
		])->execute();
		$this->userData['custom_params']['logout_time'] = date('Y-m-d H:i:s');
		$this->updateUser();
		return true;
	}
}
