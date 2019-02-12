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
	 * Get method.
	 *
	 * @return array
	 */
	public function get()
	{
		$moduleName = $this->controller->request->get('module');
		$records = $headers = [];
		$queryGenerator = $this->getQuery();
		$fieldsModel = $queryGenerator->getListViewFields();
		$limit = $queryGenerator->getLimit();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$record = ['recordLabel' => \App\Record::getLabel($row['id'])];
			foreach ($fieldsModel as $fieldName => &$fieldModel) {
				if (isset($row[$fieldName])) {
					$record[$fieldName] = $fieldModel->getDisplayValue($row[$fieldName], $row['id'], false, true);
				}
			}
			$records[$row['id']] = $record;
		}
		$dataReader->close();
		foreach ($fieldsModel as $fieldName => &$fieldModel) {
			$headers[$fieldName] = \App\Language::translate($fieldModel->getFieldLabel(), $moduleName);
		}
		$rowsCount = count($records);

		return [
			'headers' => $headers,
			'records' => $records,
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
		if ($this->getPermissionType() !== 1) {
			$this->getQueryByParentRecord($queryGenerator);
		}
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
	 * Get query by parent record.
	 *
	 * @param \App\QueryGenerator $queryGenerator
	 *
	 * @throws \Api\Core\Exception
	 */
	public function getQueryByParentRecord(\App\QueryGenerator $queryGenerator)
	{
		$parentId = $this->getParentCrmId();
		$parentModule = \App\Record::getType($parentId);
		$fields = \App\Field::getRelatedFieldForModule($queryGenerator->getModule());
		$foundField = true;
		if (\App\ModuleHierarchy::getModuleLevel($queryGenerator->getModule()) === 0) {
			$queryGenerator->addCondition('id', $parentId, 'e');
		} elseif (isset($fields[$parentModule]) && $fields[$parentModule]['name'] !== $fields[$parentModule]['relmod']) {
			$field = $fields[$parentModule];
			$queryGenerator->addNativeCondition(["{$field['tablename']}.{$field['columnname']}" => $parentId]);
		} elseif ($fields) {
			$foundField = false;
			foreach ($fields as $moduleName => $field) {
				if ($moduleName === $parentModule) {
					continue;
				}
				if ($relatedField = \App\Field::getRelatedFieldForModule($moduleName, $parentModule)) {
					$queryGenerator->addRelatedCondition([
						'sourceField' => $field['fieldname'],
						'relatedModule' => $moduleName,
						'relatedField' => $relatedField['fieldname'],
						'value' => $parentId,
						'operator' => 'e',
						'conditionGroup' => true,
					]);
					$foundField = true;
				}
			}
		}
		if (!$foundField) {
			throw new \Api\Core\Exception('Invalid module, no relationship', 400);
		}
	}
}
