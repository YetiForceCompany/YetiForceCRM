<?php
/**
 * Webservice standard container - Get users list file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebserviceStandard\Users;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - Get users list class.
 */
class RecordsList extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-condition', 'x-row-offset', 'x-row-limit', 'x-fields', 'x-order-by', 'x-parent-id'];

	/** @var \App\QueryGenerator Query generator instance. */
	protected $queryGenerator;

	/** @var \Vtiger_Field_Model[] Fields models instance. */
	protected $fields = [];

	/** @var bool Is admin. */
	protected $isAdmin;

	/**
	 * Get users list method.
	 *
	 * @api
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebserviceStandard/Users/RecordsList",
	 *		description="Gets a list of all users",
	 *		summary="List of users",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 1000", required=false, example=1000),
	 *		@OA\Parameter(name="x-row-offset", in="header", @OA\Schema(type="integer"), description="Offset, default: 0", required=false, example=0),
	 *		@OA\Parameter(name="x-order-by", in="header", description="Set the sorted results by columns [Json format]", required=false,
	 * 			@OA\JsonContent(type="object", title="Sort conditions", description="Multiple or one condition for a query generator",
	 * 				example={"field_name_1" : "ASC", "field_name_2" : "DESC"},
	 * 				@OA\AdditionalProperties(type="string", title="Sort Direction", enum={"ASC", "DESC"}),
	 * 			),
	 *		),
	 *		@OA\Parameter(name="x-fields", in="header", description="JSON array in the list of fields to be returned in response", required=false,
	 *			@OA\JsonContent(type="array", example={"field_name_1", "field_name_2"}, @OA\Items(type="string")),
	 *		),
	 *		@OA\Parameter(name="x-condition", in="header", description="Conditions [Json format]", required=false,
	 *			@OA\JsonContent(ref="#/components/schemas/Conditions-Mix-For-Query-Generator"),
	 *		),
	 *		@OA\Parameter(name="x-parent-id", in="header", @OA\Schema(type="integer"), description="Parent record id", required=false, example=5),
	 *		@OA\Response(response=200, description="List of entries",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_RecordsList_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_RecordsList_ResponseBody"),
	 *		),
	 *		@OA\Response(response=400, description="Incorrect json syntax: x-fields",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=401, description="No sent token, Invalid token, Token has expired",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=403, description="No permissions for module",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=405, description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *),
	 * @OA\Schema(
	 *		schema="Users_RecordsList_ResponseBody",
	 *		title="Users module - Response action users list",
	 *		description="Module action record list response body",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="List of records",
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
	 * 			@OA\Property(property="count", type="integer", example=54),
	 * 			@OA\Property(property="isMorePages", type="boolean", example=true),
	 * 		),
	 *	),
	 */
	public function get(): array
	{
		$this->isAdmin = \App\User::getCurrentUserModel()->isAdmin();
		$this->createQuery();
		$limit = $this->queryGenerator->getLimit();
		$isRawData = $this->isRawData();
		$response = [
			'headers' => $this->getColumnNames(),
			'records' => [],
		];
		$dataReader = $this->queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$response['records'][$row['id']] = $this->getRecordFromRow($row);
			if ($this->isAdmin && $isRawData) {
				$response['rawData'][$row['id']] = $this->getRawDataFromRow($row);
			}
		}
		$dataReader->close();
		$response['count'] = \count($response['records']);
		$response['isMorePages'] = $response['count'] === $limit;
		return $response;
	}

	/**
	 * Create query record list.
	 *
	 * @throws \Api\Core\Exception
	 */
	public function createQuery(): void
	{
		$this->queryGenerator = new \App\QueryGenerator('Users');
		$limit = 1000;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$offset = 0;
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$offset = (int) $requestOffset;
		}
		$this->queryGenerator->setLimit($limit);
		$this->queryGenerator->setOffset($offset);
		$this->queryGenerator->setCustomColumn('id');
		if ($this->isAdmin) {
			if ($requestFields = $this->controller->request->getHeader('x-fields')) {
				if (!\App\Json::isJson($requestFields)) {
					throw new \Api\Core\Exception('Incorrect json syntax: x-fields', 400);
				}
				foreach (\App\Json::decode($requestFields) as $field) {
					$this->queryGenerator->setField($field);
				}
			} else {
				$this->queryGenerator->setFields(['first_name', 'last_name', 'roleid', 'email1', 'primary_phone']);
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
		$record = ['recordLabel' => \App\Fields\Owner::getUserLabel($row['id'])];
		if ($this->isAdmin && $this->fields) {
			$moduleModel = reset($this->fields)->getModule();
			$recordModel = $moduleModel->getRecordFromArray($row);
			foreach ($this->fields as $fieldName => $fieldModel) {
				if (isset($row[$fieldName])) {
					$record[$fieldName] = $fieldModel->getUITypeModel()->getApiDisplayValue($row[$fieldName], $recordModel);
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
		if ($this->isAdmin && $this->fields) {
			foreach ($this->fields as $fieldName => $fieldModel) {
				$headers[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), 'Users');
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

		return $row;
	}
}
