<?php
/**
 * Action to check configuration.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 *
 * @OA\Info(
 * 		title="YetiForce API for Webservice App. Type: Mail",
 * 		version="0.1",
 *   	@OA\Contact(
 *     		email="devs@yetiforce.com",
 *     		name="Devs API Team",
 *     		url="https://yetiforce.com/"
 *   	),
 *   	@OA\License(
 *    		name="YetiForce Public License v3",
 *     		url="https://yetiforce.com/en/yetiforce/license"
 *   	),
 *   	termsOfService="https://yetiforce.com/"
 * )
 */

namespace Api\Mail\BaseAction;

/**
 * Action to check configuration class.
 */
final class Install extends \Api\Core\BaseAction
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
	 * @OA\Post(
	 *		path="/webservice/Install",
	 *		summary="Action to check configuration and API connection test",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *  			required=true,
	 *  			description="Content of the request",
	 *    		@OA\JsonContent(ref="#/components/schemas/InstallRequestBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/InstallRequestBody")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/InstallRequestBody")
	 *     		),
	 *	  ),
	 *    @OA\Parameter(
	 *        name="Authorization",
	 *        in="header",
	 *        required=true,
	 *        @OA\SecurityScheme(ref="#/components/schemas/securitySchemes")
	 *    ),
	 *    @OA\Parameter(
	 *        name="X-API-KEY",
	 *        in="header",
	 *        required=true,
	 *        @OA\SecurityScheme(ref="#/components/schemas/X-API-KEY")
	 *    ),
	 *    @OA\Parameter(
	 *        name="X-ENCRYPTED",
	 *        in="header",
	 *        required=true,
	 * 				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *    ),
	 *		@OA\Response(
	 *				response=200,
	 *				description="Response",
	 *				@OA\JsonContent(ref="#/components/schemas/InstallResponseBody"),
	 *		),
	 *		@OA\Response(
	 *				response=401,
	 *				description="Invalid api key"
	 * 		),
	 *		@OA\Response(
	 *				response=405,
	 *				description="Invalid method"
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
	 * 		schema="InstallRequestBody",
	 * 		title="Install request body",
	 * 		description="The body content is optional, if it is sent then the API will send it in response",
	 *		type="object",
	 * ),
	 * @OA\Schema(
	 * 		schema="InstallResponseBody",
	 * 		title="Install response body",
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
	 *    	 	type="object",
	 * 				@OA\Property(property="serverTime", type="string", format="date-time", example="2019-10-09 11:41:40", title="current time on the server"),
	 *   			@OA\Property(property="tokenExist", type="boolean", title="Does the token exist in the header?"),
	 *   			@OA\Property(property="apiKey", type="string", title="api key sent in header"),
	 *    		@OA\Property(property="data", type="object", title="Data that was sent in the request"),
	 * 				),
	 *    ),
	 * ),
	 * @OA\Tag(
	 *   name="BaseAction",
	 *   description="Basic Webservice API methods"
	 * )
	 */
	public function put()
	{
		return [
			'serverTime' => date('Y-m-d H:i:s'),
			'tokenExist' => !empty($this->controller->headers['x-token']),
			'apiKey' => $this->controller->headers['x-api-key'],
			'data' => $this->controller->request->getAllRaw()
		];
	}
}
