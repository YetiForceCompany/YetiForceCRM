<?php
/**
 * Action to check configuration.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal\BaseAction;

/**
 * Install class.
 */
class Install extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['PUT'];

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * Put method.
	 *
	 * @return array
	 *
	 * @OA\Put(
	 *		path="/webservice/Install",
	 *		summary="Install the system",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *  			required=false,
	 *  			description="Base action install request body",
	 *	  ),
	 *    @OA\Parameter(
	 *        name="X-ENCRYPTED",
	 *        in="header",
	 *        required=true,
	 * 				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *    ),
	 *		@OA\Response(
	 *				response=200,
	 *				description="Base action details",
	 *				@OA\JsonContent(ref="#/components/schemas/BaseActionInstallResponseBody"),
	 *				@OA\XmlContent(ref="#/components/schemas/BaseActionInstallResponseBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="text/html",
	 *         		@OA\Schema(ref="#/components/schemas/BaseActionInstallResponseBody")
	 *     		),
	 *		),
	 * ),
	 * @OA\Schema(
	 * 		schema="BaseActionInstallResponseBody",
	 * 		title="Base action install",
	 * 		description="The representation of a base action install",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="status",
	 *        description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - sukcess , 0 - error",
	 * 				enum={"0", "1"},
	 *     	  type="integer",
	 * 		),
	 *    @OA\Property(
	 *     	  property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="object"
	 *    ),
	 * ),
	 * @OA\Tag(
	 *   name="BaseAction",
	 *   description="Access to user methods"
	 * )
	 */
	public function put()
	{
		return $this->controller->request->getRaw('data');
	}
}
