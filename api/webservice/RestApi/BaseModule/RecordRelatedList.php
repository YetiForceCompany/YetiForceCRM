<?php
/**
 * RestApi container - Get record related list file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\RestApi\BaseModule;

use OpenApi\Annotations as OA;

/**
 * RestApi container - Get record related list class.
 */
class RecordRelatedList extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-raw-data', 'x-row-offset', 'x-row-limit', 'x-fields', 'x-parent-id', 'x-condition'];

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
	 * @return array
	 *
	 * @OA\GET(
	 *		path="/webservice/RestApi/{moduleName}/RecordRelatedList/{recordId}/{relatedModuleName}",
	 *		summary="Get the related list of records",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *		},
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="recordId",
	 *			description="Record id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="relatedModuleName",
	 *			description="Related module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="relationId",
	 *			in="query",
	 *			description="Relation id",
	 *			required=false,
	 *			@OA\Schema(type="integer"),
	 *			style="form"
	 *     ),
	 *		@OA\Parameter(
	 *			name="cvId",
	 *			in="query",
	 *			description="Custom view id",
	 *			required=false,
	 *			@OA\Schema(type="integer"),
	 *			style="form"
	 *     ),
	 *		@OA\Parameter(
	 *			name="x-raw-data",
	 *			description="Get rows limit, default: 0",
	 *			@OA\Schema(type="integer", enum={0, 1}),
	 *			in="header",
	 *			example=1,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-limit",
	 *			description="Get rows limit, default: 1000",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=1000,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-offset",
	 *			description="Offset, default: 0",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=0,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-fields",
	 *			description="JSON array in the list of fields to be returned in response",
	 *			in="header",
	 *			required=false,
	 *			@OA\JsonContent(
	 *				type="array",
	 * 				@OA\Items(type="string"),
	 * 			)
	 *		),
	 *		@OA\Parameter(
	 *			name="x-condition",
	 * 			description="Conditions [Json format]",
	 *			in="header",
	 *			required=false,
	 *			@OA\JsonContent(
	 *				description="Conditions details",
	 *				type="object",
	 *				@OA\Property(property="fieldName", description="Field name", type="string", example="lastname"),
	 *				@OA\Property(property="value", description="Search value", type="string", example="Kowalski"),
	 *				@OA\Property(property="operator", description="Field operator", type="string", example="e"),
	 *				@OA\Property(property="group", description="Condition group if true is AND", type="boolean", example=true),
	 *			),
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of consents",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_RecordRelatedList_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_RecordRelatedList_ResponseBody"),
	 *		),
	 *		@OA\Response(
	 *			response=400,
	 *			description="Relationship does not exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions to view record",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="Record doesn't exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="No relation module name",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *),
	 * @OA\Schema(
	 *		schema="BaseModule_RecordRelatedList_ResponseBody",
	 *		title="Base module - Response action related record list",
	 *		description="Module action related record list response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={"0", "1"},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="List of related records",
	 *			type="object",
	 *			@OA\Property(
	 *				property="headers",
	 *				description="Column names",
	 *				type="object",
	 *				@OA\AdditionalProperties,
	 *			),
	 *			@OA\Property(
	 *				property="records",
	 *				description="Records display details",
	 *				type="object",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Display_Details"),
	 *			),
	 *			@OA\Property(
	 *				property="rawData",
	 *				description="Records raw details",
	 *				type="object",
	 *				@OA\AdditionalProperties(type="object", ref="#/components/schemas/Record_Raw_Details"),
	 *			),
	 * 			@OA\Property(property="count", type="string", example=54),
	 * 			@OA\Property(property="isMorePages", type="boolean", example=true),
	 * 		),
	 *	),
	 */
	public function get(): array
	{
		$pagingModel = new \Vtiger_Paging_Model();
		$limit = 1000;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$pagingModel->set('limit', $limit);
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$pagingModel->set('page', (int) $requestOffset);
		}
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
		$response = [
			'headers' => [],
			'records' => [],
		];
		foreach ($relationListView->getHeaders() as $fieldName => $fieldModel) {
			$response['headers'][$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
		}
		$isRawData = $this->isRawData();
		foreach ($relationListView->getEntries($pagingModel) as $id => $relatedRecordModel) {
			$response['records'][$id] = [];
			foreach ($relationListView->getHeaders() as $fieldName => $fieldModel) {
				$value = $relatedRecordModel->get($fieldName);
				$response['records'][$id][$fieldName] = $fieldModel->getUITypeModel()->getApiDisplayValue($value, $relatedRecordModel);
				if ($isRawData) {
					$response['rawData'][$id][$fieldName] = $value;
				}
			}
		}
		$response['count'] = \count($response['records']);
		$response['isMorePages'] = $response['count'] === $limit;
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
