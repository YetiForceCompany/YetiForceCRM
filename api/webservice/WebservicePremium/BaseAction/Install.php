<?php
/**
 * Webservice premium container - Test method for the portal file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseAction;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Test method for the portal class.
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
	 * Put method - Test method for the portal.
	 *
	 * @return array
	 *
	 *	@OA\Put(
	 *		path="/webservice/WebservicePremium/Install",
	 *		summary="Test method for the portal",
	 *		description="Install the system",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}}
	 *		},
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="Base action install request body",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Put_Install_Request"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Put_Install_Request"),
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Base action details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Put_Install_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Put_Install_Response"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseAction_Put_Install_Request",
	 *		title="Base action - Install response",
	 *		description="The representation of a base action install",
	 *		type="object",
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseAction_Put_Install_Response",
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
