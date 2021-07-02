<?php
/**
 * Portal container - Install file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseAction;

use OpenApi\Annotations as OA;

/**
 * Portal container - Install class.
 */
class Install extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['PUT'];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
	}

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
	}

	/**
	 * Put method.
	 *
	 * @return array
	 *
	 *	@OA\Put(
	 *		path="/webservice/Portal/Install",
	 *		summary="Install the system",
	 *		description="Test method for the customer portal",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="Base action install request body",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Install_RequestBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Install_RequestBody"),
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Base action details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Install_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Install_ResponseBody"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseAction_Install_RequestBody",
	 *		title="Base action - Install response",
	 *		description="The representation of a base action install",
	 *		type="object",
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseAction_Install_ResponseBody",
	 *		title="Base action - Install response",
	 *		description="The representation of a base action install",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Content of responses from a given method",
	 *			type="object"
	 *		),
	 *	),
	 */
	public function put()
	{
		return $this->controller->request->getRaw('data');
	}
}
