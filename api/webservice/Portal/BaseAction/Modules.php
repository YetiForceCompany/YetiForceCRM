<?php

namespace Api\Portal\BaseAction;

/**
 * Get modules list action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Modules extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get method.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/BaseAction/Modules",
	 *		summary="Base action modules into the system",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *  			required=false,
	 *  			description="Base action modules request body",
	 *	  ),
	 *    @OA\Parameter(
	 *        name="X-ENCRYPTED",
	 *        in="header",
	 *        required=true,
	 * 				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *    ),
	 *		@OA\Response(
	 *				response=200,
	 *				description="Base action modules details",
	 *				@OA\JsonContent(ref="#/components/schemas/BaseActionModulesResponseBody"),
	 *				@OA\XmlContent(ref="#/components/schemas/BaseActionModulesResponseBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="text/html",
	 *         		@OA\Schema(ref="#/components/schemas/BaseActionModulesResponseBody")
	 *     		),
	 *		),
	 * ),
	 * @OA\Schema(
	 * 		schema="BaseActionModulesResponseBody",
	 * 		title="Base action modules",
	 * 		description="Base action modules response body",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="status",
	 *        description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - sukcess , 0 - error",
	 * 				enum={"0", "1"},
	 *     	  type="integer",
	 * 		),
	 *    @OA\Property(
	 *     	  property="result",
	 *     	 	description="Returns module permissions",
	 *    	 	type="object",
	 * 				),
	 *    ),
	 * ),
	 */
	public function get()
	{
		return \Api\Core\Module::getPermittedModules();
	}
}
