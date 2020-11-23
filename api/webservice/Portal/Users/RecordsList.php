<?php
/**
 * Get users list file.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\Users;

use OpenApi\Annotations as OA;

/**
 * Get users list class.
 */
class RecordsList extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];
	/**
	 * {@inheritdoc}
	 */
	public $allowedHeaders = ['x-condition', 'x-row-offset', 'x-row-limit', 'x-fields', 'x-row-order-field', 'x-row-order', 'x-parent-id'];
	/**
	 * Get query generator instance.
	 *
	 * @var \App\QueryGenerator
	 */
	protected $queryGenerator;
	/**
	 * Fields.
	 *
	 * @var array
	 */
	protected $fields = [];
	/**
	 * Is admin.
	 *
	 * @var bool
	 */
	protected $isAdmin;

	/**
	 * Get users list method.
	 *
	 * @return array
	 *
	 * @OA\GET(
	 *		path="/webservice/Users/RecordsList",
	 *		summary="Get the list of users",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *		},
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="The content of the request is empty",
	 *		),
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
	 *			name="x-row-order-field",
	 *			description="Sets the ORDER BY part of the query record list",
	 *			@OA\Schema(type="string"),
	 *			in="header",
	 *			example="lastname",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-order",
	 *			description="Sorting direction",
	 *			@OA\Schema(type="string", enum={"ASC", "DESC"}),
	 *			in="header",
	 *			example="DESC",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-fields",
	 *			description="JSON array in the list of fields to be returned in response",
	 *			in="header",
	 *			example={},
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
	 *		@OA\Parameter(
	 *			name="x-parent-id",
	 *			description="Parent record id",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			example=5,
	 *			required=false
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of consents",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_RecordsList_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_RecordsList_ResponseBody"),
	 *		),
	 *		@OA\Response(
	 *			response=400,
	 *			description="Incorrect json syntax",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="No sent token, Invalid token, Token has expired",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions for module",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Invalid method",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *),
	 * @OA\Schema(
	 *		schema="Users_RecordsList_ResponseBody",
	 *		title="Users - Response action users list",
	 *		description="Module action record list response body",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={"0", "1"},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="List of modules accessed",
	 *			type="object",
	 *			@OA\Property(
	 *				property="headers",
	 *				description="Column names",
	 *				type="object",
	 *				@OA\AdditionalProperties,
	 *			),
	 *			@OA\Property(
	 *				property="records",
	 *				description="List of modules accessed",
	 *				type="object",
	 *				@OA\AdditionalProperties(description="Column data", type="object"),
	 *			),
	 *			@OA\Property(
	 *				property="rawData",
	 *				description="Raw data",
	 *				type="object",
	 *				@OA\AdditionalProperties(description="Column data to display, administrator access only", type="object"),
	 *			),
	 * 			@OA\Property(property="count", type="string", example=54),
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
		if ($orderField = $this->controller->request->getHeader('x-row-order-field')) {
			$this->queryGenerator->setOrder($orderField, $this->controller->request->getHeader('x-row-order'));
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
		return 1 === (int) $this->controller->headers['x-raw-data'];
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
		return $row;
	}
}
