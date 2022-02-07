<?php
/**
 * Webservice premium container - Changes user’s preferences file.
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
 * Webservice premium container - Changes user’s preferences class.
 */
class Preferences extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['PUT'];

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
	}

	/**
	 * Put method - Changes user’s preferences.
	 *
	 * @return bool
	 *
	 * @OA\Put(
	 *		path="/webservice/WebservicePremium/Users/Preferences",
	 *		summary="Changes user’s preferences",
	 *		description="Changes user’s preferences",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\RequestBody(
	 *  		required=true,
	 *  		description="Input data",
	 *    		@OA\JsonContent(ref="#/components/schemas/Users_Put_Preferences_Request"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Put_Preferences_Request")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/Users_Put_Preferences_Request")
	 *     		),
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Response",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Put_Preferences_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Put_Preferences_Response"),
	 *		),
	 * ),
	 *	@OA\Schema(
	 * 		schema="Users_Put_Preferences_Request",
	 * 		title="Users module - Content of the request to change the user's settings",
	 *		type="object",
	 *		example={"menuPin" : 1},
	 *	),
	 * @OA\Schema(
	 * 		schema="Users_Put_Preferences_Response",
	 * 		title="Users module - Response content of changing user settings",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="boolean", description="Password change status", example=false),
	 * ),
	 */
	public function put(): bool
	{
		$data = [];
		foreach ($this->controller->request->getContentKeys() as $key) {
			$data[$key] = $this->controller->request->get($key);
		}
		$this->updateUser([
			'preferences' => $data,
		]);
		return true;
	}
}
