<?php
/**
 * Webservice standard container - Get record list file.
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
 * Webservice standard container - Get record list class.
 */
class RecordsList extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-condition', 'x-row-offset', 'x-row-limit', 'x-fields', 'x-order-by', 'x-only-column', 'x-row-count', 'x-parent-id', 'x-cv-id'];

	/** @var \App\QueryGenerator Query generator instance. */
	protected $queryGenerator;

	/** @var \Vtiger_Field_Model[] Fields models instance. */
	protected $fields = [];

	/** @var array Related fields. */
	protected $relatedFields = [];

	/** @var array Permissions. */
	protected $permissions = [];

	/**
	 * Get record list method.
	 *
	 * @api
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebserviceStandard/{moduleName}/RecordsList",
	 *		description="Gets a list of records",
	 *		summary="List of records",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 100", required=false, example=50),
	 *		@OA\Parameter(name="x-row-offset", in="header", @OA\Schema(type="integer"), description="Offset, default: 0", required=false, example=0),
	 *		@OA\Parameter(name="x-fields", in="header", description="JSON array in the list of fields to be returned in response", required=false,
	 *			@OA\JsonContent(type="array", example={"field_name_1", "field_name_2"}, @OA\Items(type="string")),
	 *		),
	 *		@OA\Parameter(name="x-condition", in="header", description="Conditions [Json format]", required=false,
	 *			@OA\JsonContent(ref="#/components/schemas/Conditions-Mix-For-Query-Generator"),
	 *		),
	 *		@OA\Parameter(name="x-only-column", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Return only column names", required=false, example=1),
	 *		@OA\Parameter(name="x-parent-id", in="header", @OA\Schema(type="integer"), description="Parent record id", required=false, example=5),
	 *		@OA\Parameter(name="x-cv-id", in="header", @OA\Schema(type="integer"), description="Custom view ID", required=false, example=5),
	 *		@OA\Parameter(name="x-order-by", in="header", description="Set the sorted results by columns [Json format]", required=false,
	 * 			@OA\JsonContent(type="object", title="Sort conditions", description="Multiple or one condition for a query generator",
	 * 				example={"field_name_1" : "ASC", "field_name_2" : "DESC"},
	 * 				@OA\AdditionalProperties(type="string", title="Sort Direction", enum={"ASC", "DESC"}),
	 * 			),
	 *		),
	 *		@OA\Response(response=200, description="List of entries",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_RecordsList_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_RecordsList_ResponseBody"),
	 *		),
	 *		@OA\Response(response=400, description="Incorrect json syntax: x-fields",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=401, description="No sent token, Invalid token, Token has expired",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=403, description="`No permissions for module` OR `No permissions for custom view: x-cv-id`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=405, description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *),
	 * @OA\Schema(
	 *		schema="BaseModule_RecordsList_ResponseBody",
	 *		title="Base module - Response action record list",
	 *		description="Module action record list response body",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="List of records",
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
	 *					@OA\Property(property="isEditable", type="boolean", example=true),
	 *					@OA\Property(property="moveToTrash", type="boolean", example=true),
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
		$this->createQuery();
		$limit = $this->queryGenerator->getLimit();
		$isRawData = $this->isRawData();
		$response = [
			'headers' => $this->getColumnNames(),
			'records' => [],
			'permissions' => [],
		];
		if ((int) $this->controller->request->getHeader('x-only-column')) {
			return $response;
		}
		if ($limit) {
			$this->queryGenerator->setLimit($limit + 1);
		}
		$query = $this->queryGenerator->createQuery();
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$response['records'][$row['id']] = $this->getRecordFromRow($row);
			$response['permissions'][$row['id']] = $this->permissions;
			if ($isRawData) {
				$response['rawData'][$row['id']] = $this->getRawDataFromRow($row);
			}
		}
		$dataReader->close();
		$response['numberOfRecords'] = \count($response['records']);
		$isMorePages = false;
		if ($limit && $response['numberOfRecords'] > $limit) {
			$key = array_key_last($response['records']);
			unset($response['records'][$key], $response['rawData'][$key]);
			$isMorePages = true;
		}
		$response['isMorePages'] = $isMorePages;
		if ($this->controller->request->getHeader('x-row-count')) {
			$response['numberOfAllRecords'] = $query->count();
		}
		return $response;
	}

	/**
	 * Create query record list.
	 *
	 * @throws \Api\Core\Exception
	 */
	public function createQuery(): void
	{
		$moduleName = $this->controller->request->getModule();
		$this->queryGenerator = new \App\QueryGenerator($moduleName);
		if ($cvId = $this->controller->request->getHeader('x-cv-id')) {
			$cv = \App\CustomView::getInstance($moduleName);
			if (!$cv->isPermittedCustomView($cvId)) {
				throw new \Api\Core\Exception('No permissions for custom view: x-cv-id', 403);
			}
			$this->queryGenerator->initForCustomViewById($cvId);
		} else {
			$this->queryGenerator->initForDefaultCustomView(false, true);
		}

		$limit = 100;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$offset = 0;
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$offset = (int) $requestOffset;
		}
		$this->queryGenerator->setLimit($limit);
		$this->queryGenerator->setOffset($offset);
		\Api\WebserviceStandard\Fields::loadWebserviceFields($this->queryGenerator->getModuleModel(), $this);
		if ($requestFields = $this->controller->request->getHeader('x-fields')) {
			if (!\App\Json::isJson($requestFields)) {
				throw new \Api\Core\Exception('Incorrect json syntax: x-fields', 400);
			}
			$this->queryGenerator->clearFields();
			foreach (\App\Json::decode($requestFields) as $field) {
				if (\is_array($field)) {
					$this->queryGenerator->addRelatedField($field);
				} else {
					$this->queryGenerator->setField($field);
				}
			}
		}
		if ($orderBy = $this->controller->request->getHeader('x-order-by')) {
			$orderBy = \App\Json::decode($orderBy);
			if (!empty($orderBy) && \is_array($orderBy)) {
				foreach ($orderBy as $fieldName => $sortFlag) {
					$field = $this->queryGenerator->getModuleField($fieldName);
					if (($field && $field->isActiveField()) || 'id' === $fieldName) {
						$this->queryGenerator->setOrder($fieldName, $sortFlag);
					}
				}
			}
		}
		$this->fields = $this->queryGenerator->getListViewFields();
		foreach ($this->queryGenerator->getRelatedFields() as $fieldInfo) {
			$this->relatedFields[$fieldInfo['relatedModule']][$fieldInfo['sourceField']][] = $fieldInfo['relatedField'];
		}
		if ($conditions = $this->controller->request->getHeader('x-condition')) {
			$conditions = \App\Json::decode($conditions);
			if (isset($conditions['fieldName'])) {
				$this->queryGenerator->addCondition($conditions['fieldName'], $conditions['value'], $conditions['operator'], $conditions['group'] ?? true, true);
			} else {
				foreach ($conditions as $condition) {
					$this->queryGenerator->addCondition($condition['fieldName'], $condition['value'], $condition['operator'], $condition['group'] ?? true, true);
				}
			}
		}
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

	/**
	 * Get record from row.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	protected function getRecordFromRow(array $row): array
	{
		$record = ['recordLabel' => \App\Record::getLabel($row['id'])];
		if ($this->fields) {
			$moduleModel = reset($this->fields)->getModule();
			$recordModel = $moduleModel->getRecordFromArray($row);
			$this->permissions = [
				'isEditable' => $recordModel->isEditable(),
				'moveToTrash' => $recordModel->privilegeToMoveToTrash(),
			];
			foreach ($this->fields as $fieldName => $fieldModel) {
				if (isset($row[$fieldName])) {
					$record[$fieldName] = $fieldModel->getUITypeModel()->getApiDisplayValue($row[$fieldName], $recordModel);
				}
			}
		}
		if ($this->relatedFields) {
			foreach ($this->relatedFields as $relatedModuleName => $fields) {
				foreach ($fields as $sourceField => $field) {
					$recordData = [
						'id' => $row[$sourceField . $relatedModuleName . 'id'] ?? 0,
					];
					foreach ($field as $relatedFieldName) {
						$recordData[$relatedFieldName] = $row[$sourceField . $relatedModuleName . $relatedFieldName];
					}
					$extRecordModel = \Vtiger_Module_Model::getInstance($relatedModuleName)->getRecordFromArray($recordData);
					foreach ($field as $relatedFieldName) {
						if ($relatedFieldModel = $extRecordModel->getField($relatedFieldName)) {
							$record["{$relatedModuleName}_{$relatedFieldName}"] = $relatedFieldModel->getUITypeModel()->getApiDisplayValue($row[$sourceField . $relatedModuleName . $relatedFieldName], $extRecordModel);
						}
					}
				}
			}
		}
		return $record;
	}

	/**
	 * Get column names.
	 *
	 * @return array
	 */
	protected function getColumnNames(): array
	{
		$headers = [];
		$selectedColumnsList = [];
		if ($cvId = $this->controller->request->getHeader('x-cv-id')) {
			$customViewModel = \CustomView_Record_Model::getInstanceById($cvId);
			$selectedColumnsList = $customViewModel->getSelectedFields();
		}
		if ($this->fields) {
			foreach ($this->fields as $fieldName => $fieldModel) {
				if ($fieldModel->isViewable()) {
					$moduleName = $fieldModel->getModuleName();
					$fieldLabel = empty($selectedColumnsList[$fieldName . ':' . $moduleName]) ? $fieldModel->getFieldLabel() : $selectedColumnsList[$fieldName . ':' . $moduleName];
					$headers[$fieldName] = \App\Language::translate($fieldLabel, $moduleName);
				}
			}
		}
		if ($this->relatedFields) {
			foreach ($this->relatedFields as $relatedModuleName => $fields) {
				foreach ($fields as $sourceField => $field) {
					foreach ($field as $relatedFieldName) {
						$fieldModel = \Vtiger_Module_Model::getInstance($relatedModuleName)->getFieldByName($relatedFieldName);
						if ($fieldModel->isViewable()) {
							$selectedColumnKey = $relatedFieldName . ':' . $relatedModuleName . ':' . $sourceField;
							$fieldLabel = empty($selectedColumnsList[$selectedColumnKey]) ? $fieldModel->getFieldLabel() : $selectedColumnsList[$selectedColumnKey];
							$headers[$sourceField . $relatedModuleName . $relatedFieldName] = \App\Language::translate($fieldLabel, $relatedModuleName);
						}
					}
				}
			}
		}
		return $headers;
	}

	/**
	 * Get raw data from row.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	protected function getRawDataFromRow(array $row): array
	{
		foreach ($this->fields as $fieldName => $fieldModel) {
			if (\array_key_exists($fieldName, $row)) {
				$row[$fieldName] = $fieldModel->getUITypeModel()->getRawValue($row[$fieldName]);
			}
		}
		if ($this->relatedFields) {
			foreach ($this->relatedFields as $relatedModuleName => $fields) {
				foreach ($fields as $sourceField => $field) {
					foreach ($field as $relatedFieldName) {
						$key = $sourceField . $relatedModuleName . $relatedFieldName;
						if (\array_key_exists($key, $row)) {
							$fieldModel = \Vtiger_Module_Model::getInstance($relatedModuleName)->getFieldByName($relatedFieldName);
							$row[$key] = $fieldModel->getUITypeModel()->getRawValue($row[$key]);
						}
					}
				}
			}
		}

		return $row;
	}
}
