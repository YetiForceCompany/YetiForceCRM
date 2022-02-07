<?php
/**
 * Webservice premium container - Gets a list of widgets file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Gets a list of widgets class.
 */
class Widgets extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** @var string[] Supported widget types */
	protected $supportedTypes = ['RelatedModule', 'Updates', 'Comments', 'DetailView'];

	/**
	 * Get widgets list method.
	 *
	 * @return array
	 *
	 *	@OA\Get(
	 *		path="/webservice/WebservicePremium/{moduleName}/Widgets",
	 *		summary="Gets a list of widgets",
	 *		description="List of widgets",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", description="Module name",	@OA\Schema(type="string"), in="path", example="Contacts", required=true),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of widgets",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_Widgets_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_Widgets_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`No sent token`, `Invalid token`, `Token has expired`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="`No permissions for module`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="`Invalid method`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Get_Widgets_Response",
	 *		title="Base module - Response action - data of widgets",
	 *		description="Module action - Data of widgets - response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(
	 *			property="status",
	 *			type="integer",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			type="object",
	 *			description="List of widgets",
	 *			@OA\AdditionalProperties(type="object", ref="#/components/schemas/BaseModule_Widget_Result"),
	 * 		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Widget_Result",
	 *		title="Widget data by specific ID",
	 *		description="Module action - Widget for specific ID - response data",
	 *		type="object",
	 *		required={"id", "type", "label", "wcol", "sequence", "name", "data"},
	 *		@OA\Property(property="id", type="integer", description="Widget ID", example=12),
	 *		@OA\Property(property="type", type="string", description="Widget type", example="RelatedModule"),
	 *		@OA\Property(property="label", type="string", description="Widget label", example="Contacts"),
	 *		@OA\Property(property="wcol", type="integer", description="Widget position", example=1, enum={1, 2, 3}),
	 *		@OA\Property(property="sequence", type="integer", description="Sequence", example=1),
	 *		@OA\Property(property="name", type="string", description="The translated name of the widget", example="Contacts"),
	 *		@OA\Property(property="data", type="object", description="Widget specific data", example={
	 *		    "relation_id" : 1,
	 *		    "relatedmodule" : 4,
	 *		    "relatedfields" : {
	 *		        "0" : "firstname",
	 *		        "1" : "lastname",
	 *		        "2" : "phone",
	 *		        "3" : "email"
	 *		    },
	 *		    "viewtype" : "List",
	 *		    "customView" : {},
	 *		    "limit" : 5,
	 *		    "action" : 1,
	 *		    "actionSelect" : "0",
	 *		    "no_result_text" : "0",
	 *		    "switchHeader" : "-",
	 *		    "filter" : "-",
	 *		    "checkbox" : "-",
	 *		    "orderby" : {},
	 *		    "relatedModuleName" : "Contacts"
	 *		}),
	 *	),
	 */
	public function get(): array
	{
		$response = [];
		$moduleName = $this->controller->request->getModule();
		$columns = array_flip(['id', 'type', 'label', 'wcol', 'sequence', 'data', 'name']);

		$dataReader = (new \App\Db\Query())->from('vtiger_widgets')
			->where(['tabid' => \App\Module::getModuleId($moduleName), 'type' => $this->supportedTypes])
			->orderBy(['tabid' => SORT_ASC, 'sequence' => SORT_ASC])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['data'] = \App\Json::decode($row['data']);
			if (empty($row['label'])) {
				if (isset(\Vtiger_Widget_Model::DEFAULT_LABELS[$row['type']])) {
					$row['name'] = \App\Language::translate(\Vtiger_Widget_Model::DEFAULT_LABELS[$row['type']], $moduleName, false, false);
				} else {
					$row['name'] = \App\Language::translate($row['data']['relatedmodule'], $row['data']['relatedmodule'], false, false);
				}
			} else {
				$row['name'] = \App\Language::translate($row['label'], $moduleName, false, false);
			}
			$widgetClass = \Vtiger_Loader::getComponentClassName('Widget', $row['type'], $moduleName);
			$widgetInstance = new $widgetClass($moduleName, null, null, $row);
			if ($row = $widgetInstance->getApiData($row)) {
				$response[$row['id']] = array_intersect_key($row, $columns);
			}
		}
		$dataReader->close();
		return $response;
	}
}
