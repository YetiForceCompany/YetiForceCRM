<?php
/**
 * Get elements of menu.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

namespace Api\Portal\BaseAction;

/**
 * Action to get menu.
 */
class Menu extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get method.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/BaseAction/Menu",
	 *		summary="Base action menu into the system",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *  			required=false,
	 *  			description="Request body does not occur",
	 *	  ),
	 *    @OA\Parameter(
	 *        name="X-ENCRYPTED",
	 *        in="header",
	 *        required=true,
	 * 				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *    ),
	 *		@OA\Response(
	 *				response=200,
	 *				description="Menu details",
	 *				@OA\JsonContent(ref="#/components/schemas/BaseActionMenuResponseBody"),
	 *				@OA\XmlContent(ref="#/components/schemas/BaseActionMenuResponseBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="text/html",
	 *         		@OA\Schema(ref="#/components/schemas/BaseActionMenuResponseBody")
	 *     		),
	 *		),
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="basicAuth",
	 *		type="http",
	 *		in="header",
	 *		scheme="basic"
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="ApiKeyAuth",
	 *		type="apiKey",
	 *		in="header",
	 *		name="X-API-KEY",
	 *		description="Webservice api key"
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="token",
	 *   	type="apiKey",
	 *    in="header",
	 * 		name="X-TOKEN",
	 *   	description="Webservice api token,"
	 * ),
	 * @OA\Schema(
	 * 		schema="BaseActionMenuResponseBody",
	 * 		title="Base action menu",
	 * 		description="Base action menu response body",
	 *		type="object",
	 *  	@OA\Property(
	 *       	property="status",
	 *        description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - sukcess , 0 - error",
	 * 				enum={"0", "1"},
	 *     	  type="integer",
	 * 		),
	 *    @OA\Property(
	 *     	  property="result",
	 *     	 	description="Menu items selected in the system, consists of parents and children",
	 *    	 	type="object",
	 * 				@OA\Property(
	 * 					property="items",
	 * 					type="object",
	 * 					title="Parent parameters",
	 * 					@OA\Property(property="id", type="integer"),
	 * 					@OA\Property(property="tabid", type="integer"),
	 * 					@OA\Property(property="mod", type="string"),
	 * 					@OA\Property(property="name", type="string"),
	 * 					@OA\Property(property="type", type="string"),
	 * 					@OA\Property(property="sequence", type="integer"),
	 * 					@OA\Property(property="newwindow", type="integer"),
	 * 					@OA\Property(property="dataurl", type="string"),
	 * 					@OA\Property(property="icon", type="string"),
	 * 					@OA\Property(property="parent", type="integer"),
	 * 					@OA\Property(property="hotkey", type="string"),
	 * 					@OA\Property(property="filters", type="string"),
	 * 					@OA\Property(
	 * 						property="childs",
	 * 						type="object",
	 * 						title="Children parameters",
	 * 				),
	 * 			),
	 *    ),
	 * ),
	 */
	public function get()
	{
		return ['items' => \Settings_Menu_Record_Model::getCleanInstance()->getChildMenu($this->controller->app['id'], 0, \Settings_Menu_Record_Model::SRC_API)];
	}
}
