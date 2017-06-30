<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_FindDuplicate_Model extends \App\Base
{

	/**
	 * Function to set module model
	 * @param Vtiger_Module_Model $moduleModel
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;
	}

	/**
	 * Function to get module model
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to get header of table
	 * @return array
	 */
	public function getListViewHeaders()
	{
		$moduleModel = $this->getModule();
		$listViewHeaders = [];
		$listViewHeaders[] = new \App\Base(['name' => 'id', 'label' => 'ID']);
		foreach (array_unique($this->headers) as $header) {
			$fieldModel = $moduleModel->getField($header);
			if ($fieldModel) {
				$listViewHeaders[] = $fieldModel;
			}
		}
		return $listViewHeaders;
	}

	/**
	 * Function to get query which searching duplicate records
	 * @return App\Db\Query $query 
	 */
	public function getQuery()
	{
		$moduleModel = $this->getModule();
		$fields = $this->get('fields');
		$fieldsModels = $this->get('selectedFieldsModels');
		$queryGenerator = new App\QueryGenerator($moduleModel->getName());
		$queryGenerator->setFields($fields);
		$queryGenerator->setField('id');
		foreach ($moduleModel->getMandatoryFieldModels() as $fieldModel) {
			$queryGenerator->setField($fieldModel->getFieldName());
		}
		if ($this->get('ignoreEmpty')) {
			foreach ($fieldsModels as $fieldModel) {
				$queryGenerator->addCondition($fieldModel->getFieldName(), '', 'ny');
			}
		}
		$this->headers = $queryGenerator->getFields();
		$query = $queryGenerator->createQuery();
		$queryGenerator->setFields($fields);
		$subQuery = $queryGenerator->createQuery(true);
		$subQuery->groupBy($fields)->andHaving((new yii\db\Expression('COUNT(*) > 1')));
		foreach ($fieldsModels as $fieldModel) {
			$orderby [$fieldModel->getFieldName()] = SORT_DESC;
			$duplicateCheckClause .= $fieldModel->getTableName() . '.' . $fieldModel->getColumnName() . ' = duplicates.' . $fieldModel->getFieldName() . ' AND ';
		}
		$query->innerJoin(['duplicates' => $subQuery], trim($duplicateCheckClause, ' AND '));
		$query->orderBy($orderby);
		return $query;
	}

	/**
	 * Function to get duplicate records
	 * @param Vtiger_Paging_Model $paging
	 * @return array
	 */
	public function getListViewEntries(Vtiger_Paging_Model $paging)
	{
		$moduleModel = $this->getModule();
		$fields = $this->get('fields');
		$fieldModels = $moduleModel->getFields();
		$selectedFieldModels = [];
		if (is_array($fields)) {
			foreach ($fieldModels as $fieldName => $fieldModel) {
				if (in_array($fieldName, $fields)) {
					$selectedFieldModels [] = $fieldModel;
					$diffColumns[] = $fieldName;
				}
			}
		}
		$this->set('selectedFieldsModels', $selectedFieldModels);
		$startIndex = $paging->getStartIndex();
		$pageLimit = $paging->getPageLimit();
		$query = $this->getQuery();
		$query->limit($pageLimit + 1)->offset($startIndex);
		$entries = $query->all();
		$rows = count($entries);
		$group = 'group0';
		$temp = $fieldValues = [];
		$groupCount = 0;
		$groupRecordCount = 0;
		$paging->calculatePageRange($rows);
		if ($rows > $pageLimit) {
			array_pop($entries);
			$paging->set('nextPageExists', true);
		} else {
			$paging->set('nextPageExists', false);
		}
		for ($i = 0; $i < $rows; $i++) {
			$row = $entries[$i];
			if (!$row) {
				continue;
			}
			if ($i != 0) {
				$slicedArray = [];
				foreach ($diffColumns as $diffColumn) {
					$slicedArray[$diffColumn] = $row[$diffColumn];
				}
				array_walk($temp, 'lower_array');
				array_walk($slicedArray, 'lower_array');
				$arrDiff = array_diff($temp, $slicedArray);
				if (count($arrDiff) > 0) {
					$groupCount++;
					$temp = $slicedArray;
					$groupRecordCount = 0;
				}
				$group = 'group' . $groupCount;
			}
			foreach ($row as $field => $value) {
				if (in_array($field, $diffColumns)) {
					$temp[$field] = $value;
				}
				$fieldModel = $fieldModels[$field];
				$resultRow[$field] = $value;
			}
			$fieldValues[$group][$groupRecordCount++] = $resultRow;
		}
		return $fieldValues;
	}

	/**
	 * Function to get instance
	 * @param string $module
	 * @return \self
	 */
	public static function getInstance($module)
	{
		$self = new self();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$self->setModule($moduleModel);
		return $self;
	}

	/**
	 * Function to get numbers all records
	 * @return int
	 */
	public function getRecordCount()
	{
		$moduleModel = $this->getModule();
		$fields = $this->get('fields');
		$selectedFieldModels = [];
		if (is_array($fields)) {
			foreach ($fields as $fieldName ) {
				$selectedFieldModels [] = $moduleModel->getField($fieldName);
			}
		}
		$this->set('selectedFieldsModels', $selectedFieldModels);
		$query = $this->getQuery();
		$query->orderBy([]);
		return $query->count();
	}

	/**
	 * Function to get ids of records
	 * @param \App\Request $request
	 * @return int[]
	 */
	public static function getMassDeleteRecords(\App\Request $request)
	{
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$fields = $request->get('fields');
		$selectedFieldModels = [];
		if (is_array($fields)) {
			foreach ($fields as $fieldName ) {
				$selectedFieldModels [] = $moduleModel->getField($fieldName);
			}
		}
		$findDuplicatesModel = self::getInstance($module);
		$findDuplicatesModel->set('ignoreEmpty', $request->get('ignoreEmpty') === 'on');
		$findDuplicatesModel->set('fields', $fields);
		$findDuplicatesModel->set('selectedFieldsModels', $selectedFieldModels);
		$dataReader = $findDuplicatesModel->getQuery()->createCommand()->query();
		$recordIds = [];
		$excludedIds = $request->get('excluded_ids');
		while ($record = $dataReader->read()) {
			$recordIds []= $record['id'];
		}
		return array_diff($recordIds, $excludedIds);
	}
}
