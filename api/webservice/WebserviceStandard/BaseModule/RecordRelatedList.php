<?php
/**
 * Webservice standard container - Get record related list file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebserviceStandard\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - Get record related list class.
 */
class RecordRelatedList extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-raw-data', 'x-row-offset', 'x-row-limit', 'x-fields', 'x-parent-id', 'x-condition', 'x-order-by', 'x-row-count'];

	/** @var \Vtiger_Record_Model Record model instance. */
	protected $recordModel;

	/** {@inheritdoc}  */
	public function checkAction(): void
	{
		parent::checkAction();
		if ($this->controller->request->isEmpty('param', 'Alnum')) {
			throw new \Api\Core\Exception('No relation module name', 405);
		}
		$moduleName = $this->controller->request->getModule();
		if ($this->controller->request->isEmpty('record', true) || !\App\Record::isExists($this->controller->request->getInteger('record'), $moduleName)) {
			throw new \Api\Core\Exception('Record doesn\'t exist', 404);
		}
		$this->recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $moduleName);
		if (!$this->recordModel->isViewable()) {
			throw new \Api\Core\Exception('No permissions to view record', 403);
		}
	}

	/**
	 * Get related record list method.
	 *
	 * @api
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebserviceStandard/{moduleName}/RecordRelatedList/{recordId}/{relatedModuleName}",
	 *		description="Gets a list of related records",
	 *		summary="Related list of records",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="relatedModuleName", in="path", @OA\Schema(type="string"), description="Related module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="relationId", in="query", @OA\Schema(type="integer"), style="form", description="Relation id", required=false),
	 *		@OA\Parameter(name="cvId", in="query", @OA\Schema(type="integer"), style="form", description="Custom view id", required=false),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 100", required=false, example=1000),
	 *		@OA\Parameter(name="x-row-offset", in="header", @OA\Schema(type="integer"), description="Offset, default: 0", required=false, example=0),
	 *		@OA\Parameter(name="x-fields", in="header", description="JSON array in the list of fields to be returned in response", required=false,
	 *			@OA\JsonContent(type="array", example={"field_name_1", "field_name_2"}, @OA\Items(type="string")),
	 *		),
	 *		@OA\Parameter(name="x-condition", in="header", description="Conditions [Json format]", required=false,
	 *			@OA\JsonContent(ref="#/components/schemas/Conditions-Mix-For-Query-Generator"),
	 *		),
	 *		@OA\Parameter(name="x-only-column", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Return only column names", required=false, example=1),
	 *		@OA\Parameter(name="x-order-by", in="header", description="Set the sorted results by columns [Json format]", required=false,
	 * 			@OA\JsonContent(type="object", title="Sort conditions", description="Multiple or one condition for a query generator",
	 * 				example={"field_name_1" : "ASC", "field_name_2" : "DESC"},
	 * 				@OA\AdditionalProperties(type="string", description="Sort Direction", enum={"ASC", "DESC"}),
	 * 			),
	 *		),
	 *		@OA\Response(response=200, description="List of entries",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_RecordRelatedList_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_RecordRelatedList_ResponseBody"),
	 *		),
	 *		@OA\Response(response=400, description="Relationship does not exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=403, description="No permissions to view record",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=404, description="Record doesn't exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=405, description="No relation module name",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *  ),
	 * @OA\Schema(
	 *		schema="BaseModule_RecordRelatedList_ResponseBody",
	 *		title="Base module - Response action related record list",
	 *		description="Module action related record list response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="List of related records",
	 *			required={"headers", "records", "permissions", "numberOfRecords", "isMorePages"},
	 *			@OA\Property(property="headers", type="object", title="Fields names", example={"field_name_1" : "Field label 1", "field_name_2" : "Field label 2", "assigned_user_id" : "Assigned user", "createdtime" : "Created time"},
	 * 				@OA\AdditionalProperties(type="string", description="Field name"),
	 *			),
	 *			@OA\Property(property="records", type="object", title="Records display details",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Display_Details"),
	 *			),
	 *			@OA\Property(property="permissions", type="object", title="Records action permissions",
	 *				@OA\AdditionalProperties(type="object", title="Record action permissions",
	 *					required={"isEditable", "moveToTrash"},
	 *					@OA\Property(property="isEditable", type="boolean", description="Check if record is editable", example=true),
	 *					@OA\Property(property="moveToTrash", type="boolean", description="Permission to delete", example=true),
	 *				),
	 *			),
	 *			@OA\Property(property="rawData", type="object", title="Records raw details, dependent on the header `x-raw-data`",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Raw_Details"),
	 *			),
	 * 			@OA\Property(property="numberOfRecords", type="integer", description="Number of records on the page", example=20),
	 * 			@OA\Property(property="isMorePages", type="boolean", description="There are more pages", example=true),
	 * 			@OA\Property(property="numberOfAllRecords", type="integer", description="Number of all records, dependent on the header `x-row-count`", example=54),
	 * 		),
	 *	),
	 */
	public function get(): array
	{
		$limit = 100;
		$relationModuleName = $this->controller->request->getByType('param', 'Alnum');
		$relationId = false;
		$cvId = 0;
		if ($this->controller->request->has('relationId')) {
			$relationId = $this->controller->request->getInteger('relationId');
		}
		if ($this->controller->request->has('cvId')) {
			$cvId = $this->controller->request->getInteger('cvId');
		}
		$relationListView = \Vtiger_RelationListView_Model::getInstance($this->recordModel, $relationModuleName, $relationId, $cvId);
		if (!$relationListView) {
			throw new \Api\Core\Exception('Relationship does not exist', 400);
		}
		if ($requestFields = $this->controller->request->getHeader('x-fields')) {
			$relationListView->setFields(\array_merge(['id'], \App\Json::decode($requestFields)));
		}
		$response = [
			'headers' => [],
			'records' => [],
			'permissions' => [],
		];
		foreach ($relationListView->getHeaders() as $fieldName => $fieldModel) {
			$response['headers'][$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
		}
		if ((int) $this->controller->request->getHeader('x-only-column')) {
			return $response;
		}
		if ($conditions = $this->controller->request->getHeader('x-condition')) {
			$conditions = \App\Json::decode($conditions);
			if (isset($conditions['fieldName'])) {
				$relationListView->getQueryGenerator()->addCondition($conditions['fieldName'], $conditions['value'], $conditions['operator'], $conditions['group'] ?? true, true);
			} else {
				foreach ($conditions as $condition) {
					$relationListView->getQueryGenerator()->addCondition($condition['fieldName'], $condition['value'], $condition['operator'], $condition['group'] ?? true, true);
				}
			}
		}
		if ($orderBy = $this->controller->request->getHeader('x-order-by')) {
			$orderBy = \App\Json::decode($orderBy);
			if (!empty($orderBy) && \is_array($orderBy)) {
				foreach ($orderBy as $fieldName => $sortFlag) {
					$field = $relationListView->getRelatedModuleModel()->getFieldByName($fieldName);
					if (($field && $field->isActiveField()) || 'id' === $fieldName) {
						$relationListView->getQueryGenerator()->setOrder($fieldName, $sortFlag);
					}
				}
			}
		}
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$relationListView->getQueryGenerator()->setLimit($limit + 1);
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$relationListView->getQueryGenerator()->setOffset((int) $requestOffset);
		}
		$isRawData = $this->isRawData();
		foreach ($relationListView->getAllEntries() as $id => $relatedRecordModel) {
			$response['permissions'][$id] = [
				'isEditable' => $relatedRecordModel->isEditable(),
				'moveToTrash' => $relatedRecordModel->privilegeToMoveToTrash(),
			];
			$response['records'][$id] = [];
			foreach ($relationListView->getHeaders() as $fieldName => $fieldModel) {
				$value = $relatedRecordModel->get($fieldName);
				$response['records'][$id][$fieldName] = $fieldModel->getUITypeModel()->getApiDisplayValue($value, $relatedRecordModel);
				if ($isRawData) {
					$response['rawData'][$id][$fieldName] = $relatedRecordModel->getRawValue($fieldName);
				}
			}
		}
		$response['numberOfRecords'] = \count($response['records']);
		$isMorePages = false;
		if ($limit && $response['numberOfRecords'] > $limit) {
			$key = array_key_last($response['records']);
			unset($response['records'][$key], $response['rawData'][$key], $response['permissions'][$key]);
			$isMorePages = true;
		}
		$response['isMorePages'] = $isMorePages;
		if ($this->controller->request->getHeader('x-row-count')) {
			$response['numberOfAllRecords'] = $relationListView->getRelationQuery()->count();
		}
		return $response;
	}

	/**
	 * Check if you send raw data.
	 *
	 * @return bool
	 */
	protected function isRawData(): bool
	{
		return 1 === (int) ($this->controller->headers['x-raw-data'] ?? 0);
	}
}
