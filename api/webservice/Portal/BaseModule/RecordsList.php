<?php

namespace Api\Portal\BaseModule;

/**
 * Get record list class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	 * Get method.
	 *
	 * @return array
	 *
	 * @OA\GET(
	 *		path="/webservice/{moduleName}/RecordsList",
	 *		summary="Gets the list of consents",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *	},
	 *		@OA\RequestBody(
	 *				required=false,
	 *				description="The content of the request is empty",
	 *		),
	 *		@OA\Parameter(
	 *			name="x-condition",
	 * 			description="Add conditions [Json format]",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_RecordsList_ResponseBody"),
	 *			in="header",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-offset",
	 *			description="Offset",
	 *			@OA\Schema(
	 *				type="integer",
	 *				format="int64",
	 *			),
	 *			in="header",
	 *			example="0",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-limit",
	 *			description="Get row limit",
	 *			@OA\Schema(
	 *				type="integer",
	 *				format="int64",
	 *			),
	 *			in="header",
	 *			example="0",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-fields",
	 *			description="Get fields",
	 *			@OA\Schema(
	 * 				type="string",
	 *			),
	 *			in="header",
	 *			example="1",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-order-field",
	 *			description="Get order field",
	 *			@OA\Schema(
	 *				type="alnumExtended",
	 *			),
	 *			in="header",
	 *			example="1",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-row-order",
	 *			description="Get row order",
	 *			@OA\Schema(
	 *			type="alnum"
	 *			),
	 *			in="header",
	 *			example="1",
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-parent-id",
	 *			description="Get parent id",
	 *			@OA\Schema(
	 *			type="integer",
	 *			format="int64",
	 *			),
	 *			in="header",
	 *			example="1",
	 *			required=false
	 *		),
	 *		@OA\Response(
	 *				response=200,
	 *				description="List of consents",
	 *				@OA\JsonContent(ref="#/components/schemas/BaseModule_RecordsList_ResponseBody"),
	 *				@OA\MediaType(
	 *						mediaType="text/html",
	 *						@OA\Schema(ref="#/components/schemas/BaseModule_RecordsList_ResponseBody")
	 *				),
	 *		),
	 *),
	 * @OA\Schema(
	 *		schema="BaseModule_RecordsList_ResponseBody",
	 *		title="Base module - Response action record list",
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
	 *			description="Information about sent data",
	 *			type="object",
	 *			@OA\AdditionalProperties(property="headers", description="Column names", type="string", example="accountname"),
	 *			@OA\AdditionalProperties(
	 *				property="records",
	 *				description="Contains field names with values",
	 *				type="object",
	 *				@OA\Property(property="recordLabel", description="Get value form field", type="string", example="Kowalski Adam"),
	 *			),
	 *			@OA\AdditionalProperties(
	 *				property="rawData",
	 *				type="object",
	 *				@OA\Property(
	 *					property="24862",
	 *					description="Raw data from row",
	 *					type="object",
	 *					@OA\Property(property="id", description="Consent ID", type="integer", example=24862),
	 *				),
	 *			),
	 *			@OA\AdditionalProperties(property="count", description="Number of records", type="intiger", example="10"),
	 *			@OA\AdditionalProperties(property="isMorePages", description="There are more entries", type="boolean", example="true"),
	 *		),
	 *	),
	 */
	public function get()
	{
		$rawData = $records = $headers = [];
		$queryGenerator = $this->getQuery();
		$fieldsModel = $queryGenerator->getListViewFields();
		$limit = $queryGenerator->getLimit();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$records[$row['id']] = $this->getRecordFromRow($row, $fieldsModel);
			if ($this->isRawData()) {
				$rawData[$row['id']] = $this->getRawDataFromRow($row);
			}
		}
		$dataReader->close();
		$headers = $this->getColumnNames($fieldsModel);
		$rowsCount = \count($records);
		return [
			'headers' => $headers,
			'records' => $records,
			'rawData' => $rawData,
			'count' => $rowsCount,
			'isMorePages' => $rowsCount === $limit,
		];
	}

	/**
	 * Get query record list.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return \App\QueryGenerator
	 */
	public function getQuery()
	{

		$queryGenerator = new \App\QueryGenerator($this->controller->request->getModule());
		$queryGenerator->initForDefaultCustomView();
		$limit = 1000;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}

		if ($orderField = $this->controller->request->getHeader('x-row-order-field')) {
			$queryGenerator->setOrder($orderField, $this->controller->request->getHeader('x-row-order'));
		}

		$offset = 0;
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$offset = (int) $requestOffset;
		}
		$queryGenerator->setLimit($limit);
		$queryGenerator->setOffset($offset);
		if ($requestFields = $this->controller->request->getHeader('x-fields')) {
			$queryGenerator->setFields(\App\Json::decode($requestFields));
			$queryGenerator->setField('id');
		}

		if ($conditions = $this->controller->request->getHeader('x-condition')) {
			$conditions = \App\Json::decode($conditions);
			if (isset($conditions['fieldName'])) {
				$queryGenerator->addCondition($conditions['fieldName'], $conditions['value'], $conditions['operator'], $conditions['group'] ?? true, true);
			} else {
				foreach ($conditions as $condition) {
					$queryGenerator->addCondition($condition['fieldName'], $condition['value'], $condition['operator'], $condition['group'] ?? true, true);
				}
			}
		}
		return $queryGenerator;
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
	 * @param array                 $row
	 * @param \Vtiger_Field_Model[] $fieldsModel
	 *
	 * @return array
	 */
	protected function getRecordFromRow(array $row, array $fieldsModel): array
	{
		$record = ['recordLabel' => \App\Record::getLabel($row['id'])];
		$recordModel = \Vtiger_Record_Model::getCleanInstance($this->controller->request->getModule());
		foreach ($fieldsModel as $fieldName => &$fieldModel) {
			if (isset($row[$fieldName])) {
				$recordModel->set($fieldName, $row[$fieldName]);
				$record[$fieldName] = $recordModel->getDisplayValue($fieldName, $row['id'], true);
			}
		}
		return $record;
	}

	/**
	 * Get column names.
	 *
	 * @param array $fieldsModel
	 *
	 * @return array
	 */
	protected function getColumnNames(array $fieldsModel): array
	{
		$headers = [];
		foreach ($fieldsModel as $fieldName => $fieldModel) {
			$headers[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
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
