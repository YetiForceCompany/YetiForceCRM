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
	 * Module name.
	 *
	 * @var string
	 */
	private $moduleName;

	/**
	 * Get method.
	 *
	 * @return array
	 */
	public function get()
	{
		$this->moduleName = $this->controller->request->get('module');
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
		$rowsCount = count($records);
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
		$queryGenerator = new \App\QueryGenerator($this->controller->request->get('module'));
		$queryGenerator->initForDefaultCustomView();
		$limit = 1000;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
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
				$queryGenerator->addCondition($conditions['fieldName'], $conditions['value'], $conditions['operator']);
			} else {
				foreach ($conditions as $condition) {
					$queryGenerator->addCondition($condition['fieldName'], $condition['value'], $condition['operator']);
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
		foreach ($fieldsModel as $fieldName => &$fieldModel) {
			if (isset($row[$fieldName])) {
				$record[$fieldName] = $fieldModel->getDisplayValue($row[$fieldName], $row['id'], false, true);
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
			$headers[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
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
