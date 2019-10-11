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
	 *		path="/webservice/BaseAction/Install",
	 *		summary="Install the system",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *  			required=false,
	 *  			description="Input data format",
	 *	  ),
	 *    @OA\Parameter(
	 *        name="X-ENCRYPTED",
	 *        in="header",
	 *        required=true,
	 * 				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *    ),
	 *		@OA\Response(
	 *				response=200,
	 *				description="User details",
	 *				@OA\JsonContent(ref="#/components/schemas/BaseActionInstallResponseBody"),
	 *				@OA\XmlContent(ref="#/components/schemas/BaseActionInstallResponseBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="text/html",
	 *         		@OA\Schema(ref="#/components/schemas/BaseActionInstallResponseBody")
	 *     		),
	 *		),
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="basicAuth",
	 *		type="http",
	 *    in="header",
	 *		scheme="basic"
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="ApiKeyAuth",
	 *   	type="apiKey",
	 *    in="header",
	 * 		name="X-API-KEY",
	 *   	description="Webservice api key"
	 * ),
	 * @OA\Schema(
	 *	  schema="X-ENCRYPTED",
	 *		type="string",
	 *  	description="Is the content request is encrypted",
	 *  	enum={"0", "1"},
	 *   	default="0"
	 * ),
	 * @OA\Schema(
	 * 		schema="BaseActionInstallResponseBody",
	 * 		title="Base action install response body",
	 * 		description="JSON data",
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
	 *    	 	type="array"
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
