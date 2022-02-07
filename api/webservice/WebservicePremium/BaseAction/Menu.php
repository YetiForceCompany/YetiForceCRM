<?php
/**
 * Webservice premium container - Gets the menu for the portal file.
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
 * Webservice premium container - Gets the menu for the portal class.
 */
class Menu extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/**
	 * Get method - Gets the menu for the portal.
	 *
	 * @return array
	 *
	 *	@OA\Get(
	 *		path="/webservice/WebservicePremium/Menu",
	 *		summary="Gets the menu for the portal",
	 *		description="Get menu",
	 *		tags={"BaseAction"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Menu details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Get_Menu_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Get_Menu_Response"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseAction_Get_Menu_Response",
	 *		title="Base action - Menu",
	 *		description="Base action menu response body",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Menu items selected in the system, consists of parents and children",
	 *			type="object",
	 *			@OA\Property(
	 *				property="items",
	 *				type="object",
	 *				title="Parent parameters",
	 *				@OA\AdditionalProperties(
	 *					description="Tree item",
	 *					type="object",
	 *					@OA\Property(property="id", type="integer", example=171),
	 * 					@OA\Property(property="tabid", type="integer", example=3),
	 * 					@OA\Property(property="mod", type="string", example="Accounts"),
	 * 					@OA\Property(property="name", type="string", example="Accounts"),
	 * 					@OA\Property(property="type", type="string", example="Module"),
	 * 					@OA\Property(property="sequence", type="integer", example=1),
	 * 					@OA\Property(property="newwindow", type="integer", example=0),
	 * 					@OA\Property(property="dataurl", type="string", example="index.php?module=Module&view=List&mid=172"),
	 * 					@OA\Property(property="icon", type="string", example="dminIcon-shared-owner"),
	 * 					@OA\Property(property="parent", type="integer", example=0),
	 * 					@OA\Property(property="hotkey", type="string", example="ctrl+k"),
	 * 					@OA\Property(property="filters", type="string", example="4,130"),
	 * 					@OA\Property(property="childs", type="object", title="Children parameters"),
	 * 					@OA\Property(property="label", type="string", example="My home page"),
	 * 				),
	 * 			),
	 *		),
	 * ),
	 */
	public function get(): array
	{
		$menu = \Settings_Menu_Record_Model::getCleanInstance()->getChildMenu($this->controller->app['id'], 0, \Settings_Menu_Record_Model::SRC_API);
		return [
			'items' => \Settings_Menu_Record_Model::parseToDisplay($menu),
		];
	}
}
