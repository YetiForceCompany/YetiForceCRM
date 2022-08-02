<?php
/**
 * Webservice standard container for custom view - file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebserviceStandard\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container for custom view - class.
 */
class CustomView extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-cv-id'];

	/** @var int Custom view ID. */
	protected $cvId;

	/** @var \App\CustomView Custom view instance. */
	protected $customView;

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		$this->customView = \App\CustomView::getInstance($this->controller->request->getModule());
		if ($this->controller->request->has('record')) {
			$this->cvId = $this->controller->request->getInteger('record');
			if (!$this->customView->isPermittedCustomView($this->cvId)) {
				throw new \Api\Core\Exception('No permissions to view record', 403);
			}
		}
	}

	/**
	 * Get custom view list method.
	 *
	 * @api
	 *
	 * @return array
	 *
	 *	@OA\Get(
	 *		path="/webservice/WebserviceStandard/{moduleName}/CustomView",
	 *		description="Gets a list of custom view",
	 *		summary="List of custom view",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of custom view",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_CustomView_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_CustomView_Response"),
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
	 *	),
	 *	@OA\Get(
	 *		path="/webservice/WebserviceStandard/{moduleName}/CustomView/{cvId}",
	 *		operationId="Api\WebserviceStandard\BaseModule\CustomView::get(cvId)",
	 *		description="Gets data of custom view",
	 *		summary="Data of custom view",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 * 		@OA\Parameter(
	 *			name="cvId",
	 *			description="Custom view ID",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=12,
	 *			required=true
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Data of custom view",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_CustomViewById_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_CustomViewById_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`No sent token`, `Invalid token`, `Token has expired`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="`No permissions to view record` OR `No permissions for module or data provided in the request`",
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
	 *		schema="BaseModule_Get_CustomView_Response",
	 *		title="Base module - Response action - data of custom view list",
	 *		description="Module action - Data of custom view list - response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			type="object",
	 *			description="List of custom view",
	 *			@OA\AdditionalProperties(type="object", ref="#/components/schemas/BaseModule_CustomViewById_Result"),
	 * 		),
	 *	),
	 * @OA\Schema(
	 *		schema="BaseModule_Get_CustomViewById_Response",
	 *		title="Base module - Response action - data of custom view by specific ID",
	 *		description="Module action - custom view for specific ID - response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			type="object",
	 *			description="Data of custom view",
	 *			ref="#/components/schemas/BaseModule_CustomViewById_Result",
	 * 		),
	 *	),
	 * @OA\Schema(
	 *		schema="BaseModule_CustomViewById_Result",
	 *		title="Data of custom view by specific ID",
	 *		description="Module action - custom view for specific ID - response data",
	 *		type="object",
	 *		required={"cvid", "viewname", "entitytype", "sequence", "description", "color", "isFeatured", "isDefault"},
	 *		@OA\Property(property="cvid", type="integer", description="Custom view ID", example=12),
	 *		@OA\Property(property="viewname", type="string", description="Custom view name", example="Test 1"),
	 *		@OA\Property(property="entitytype", type="string", description="Module name", example="Accounts"),
	 *		@OA\Property(property="sequence", type="integer", description="Sequence", example=1),
	 *		@OA\Property(property="description", type="string", description="Custom view description", example="Description"),
	 *		@OA\Property(property="color", type="string", description="Color for custom view", example="#c28306"),
	 *		@OA\Property(property="isFeatured", type="boolean", description="Custom view is in favorites", example=false),
	 *		@OA\Property(property="isDefault", type="boolean", description="Custom view is default", example=false),
	 *	),
	 */
	public function get(): array
	{
		$response = [];
		$moduleName = $this->controller->request->getModule();
		$columns = array_flip(['cvid', 'viewname', 'entitytype', 'sequence', 'description', 'color']);

		$defaultId = $this->customView->getViewId(true);
		if ($this->cvId) {
			$recordModel = \CustomView_Record_Model::getInstanceById($this->cvId);
			$response = array_intersect_key($recordModel->getData(), $columns);
			$response['viewname'] = \App\Language::translate(\App\Purifier::decodeHtml($recordModel->getName()), $moduleName, false, false);
			$response['isFeatured'] = $recordModel->isFeatured();
			$response['isDefault'] = $defaultId === $recordModel->getId();
		} else {
			foreach (\CustomView_Record_Model::getAll($moduleName, false) as $cvId => $recordModel) {
				$response[$cvId] = array_intersect_key($recordModel->getData(), $columns);
				$response[$cvId]['viewname'] = \App\Language::translate(\App\Purifier::decodeHtml($recordModel->getName()), $moduleName, false, false);
				$response[$cvId]['isFeatured'] = $recordModel->isFeatured();
				$response[$cvId]['isDefault'] = $defaultId === $recordModel->getId();
			}
		}
		return $response;
	}
}
