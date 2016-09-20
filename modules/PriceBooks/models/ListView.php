<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PriceBooks_ListView_Model extends Vtiger_ListView_Model
{

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel, $searchResult = false)
	{
		$db = PearDatabase::getInstance();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');

		$srcRecord = $this->get('src_record');
		if ($moduleName == $this->get('src_module') && !empty($srcRecord)) {
			$queryGenerator->addCondition('id', $srcRecord, 'n');
		}

		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}

		$glue = '';
		if (count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
			$glue = QueryGenerator::$AND;
		}
		$queryGenerator->parseAdvFilterList($searchParams, $glue);

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if (!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');

		if (!empty($orderBy)) {
			$columnFieldMapping = $moduleModel->getColumnFieldMapping();
			$orderByFieldName = $columnFieldMapping[$orderBy];
			$orderByFieldModel = $moduleModel->getField($orderByFieldName);
			if ($orderByFieldModel && ($orderByFieldModel->isReferenceField() || $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::CURRENCY_LIST)) {
				//IF it is reference add it in the where fields so that from clause will be having join of the table
				$queryGenerator = $this->get('query_generator');
				$queryGenerator->setConditionField($orderByFieldName);
			}
		}

		$listQuery = $this->getQuery();
		if ($searchResult && $searchResult != '' && is_array($searchResult)) {
			$listQuery .= ' && vtiger_crmentity.crmid IN (' . implode(',', $searchResult) . ') ';
		}
		unset($searchResult);

		$sourceModule = $this->get('src_module');
		$sourceField = $this->get('src_field');
		if (!empty($sourceModule)) {
			if (method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery, $this->get('currency_id'));
				if (!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();
		$viewid = ListViewSession::getCurrentView($moduleName);
		if (empty($viewid)) {
			$viewid = $pagingModel->get('viewid');
		}
		$_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');
		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		//For Pricebooks popup in Products and Services Related list
		if ($sourceField !== 'productsRelatedList') {
			$listQuery .= " LIMIT $startIndex," . ($pageLimit + 1);
		}

		$listResult = $db->pquery($listQuery);

		$listViewRecordModels = [];
		$listViewEntries = $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);

		$pagingModel->calculatePageRange($listViewEntries);

		//To check if next page
		if ($db->num_rows($listResult) > $pageLimit && $sourceField !== 'productsRelatedList') {
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		$index = 0;
		foreach ($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;

			// Pass through the src_record state to dependent model
			if ($this->has('src_record')) {
				$rawData['src_record'] = $this->get('src_record');
			}

			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
			$listViewRecordModels[$recordId]->colorList = Settings_DataAccess_Module_Model::executeColorListHandlers($moduleName, $recordId, $moduleModel->getRecordFromArray($listViewContoller->rawData[$recordId]));
		}
		return $listViewRecordModels;
	}

	public function getListViewOrderBy()
	{
		$moduleModel = $this->getModule();

		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');
		if (!empty($orderBy)) {
			$columnFieldMapping = $moduleModel->getColumnFieldMapping();
			$orderByFieldName = $columnFieldMapping[$orderBy];
			$orderByFieldModel = $moduleModel->getField($orderByFieldName);
			if ($orderByFieldModel && $orderByFieldModel->isReferenceField()) {
				//IF it is reference add it in the where fields so that from clause will be having join of the table
				$this->get('query_generator')->setConditionField($orderByFieldName);

				$referenceModules = $orderByFieldModel->getReferenceList();
				$referenceNameFieldOrderBy = [];
				foreach ($referenceModules as $referenceModuleName) {
					$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModuleName);
					$referenceNameFields = $referenceModuleModel->getNameFields();

					$columnList = [];
					foreach ($referenceNameFields as $nameField) {
						$fieldModel = $referenceModuleModel->getField($nameField);
						$columnList[] = $fieldModel->get('table') . $orderByFieldModel->getName() . '.' . $fieldModel->get('column');
					}
					if (count($columnList) > 1) {
						$referenceNameFieldOrderBy[] = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => $columnList[0], 'last_name' => $columnList[1]), 'Users', '') . ' ' . $sortOrder;
					} else {
						$referenceNameFieldOrderBy[] = implode('', $columnList) . ' ' . $sortOrder;
					}
				}
				$query = sprintf(' ORDER BY %s', implode(',', $referenceNameFieldOrderBy));
			} else if ($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::CURRENCY_LIST) {
				$this->get('query_generator')->setConditionField($orderByFieldName);
				$query = sprintf(' ORDER BY %s %s', $orderByFieldModel->getUITypeModel()->getCurrenyListReferenceFieldName(), $sortOrder);
			} else if ($orderBy === 'smownerid') {
				$this->get('query_generator')->setConditionField($orderByFieldName);
				$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
				if ($fieldModel->getFieldDataType() == 'owner') {
					$orderBy = 'COALESCE(' . \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . ',vtiger_groups.groupname)';
				}
				$query = sprintf(' ORDER BY %s %s', $orderBy, $sortOrder);
			} else {
				$query = sprintf(' ORDER BY %s $s', $orderBy, $sortOrder);
			}
		}
		return $query;
	}

	public function loadListViewCondition($moduleName)
	{
		$queryGenerator = $this->get('query_generator');
		$srcRecord = $this->get('src_record');
		if ($moduleName == $this->get('src_module') && !empty($srcRecord)) {
			$queryGenerator->addCondition('id', $srcRecord, 'n');
		}

		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}
		$glue = '';
		if (count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
			$glue = QueryGenerator::$AND;
		}
		$queryGenerator->parseAdvFilterList($searchParams, $glue);

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if (!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(
				[
					'search_field' => $searchKey,
					'search_text' => $searchValue,
					'operator' => $operator
				]
			);
		}
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewCount()
	{
		$db = PearDatabase::getInstance();

		$queryGenerator = $this->get('query_generator');
		$moduleName = $this->getModule()->get('name');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}

		$glue = '';
		if (count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
			$glue = QueryGenerator::$AND;
		}
		$queryGenerator->parseAdvFilterList($searchParams, $glue);

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if (!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		$listQuery = $this->getQuery();
		$sourceModule = $this->get('src_module');
		if (!empty($sourceModule)) {
			if (method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery, $this->get('currency_id'));
				if (!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}
		$position = stripos($listQuery, ' from ');
		if ($position) {
			$split = preg_split('/ from /i', $listQuery, 2);
			$listQuery = 'SELECT count(*) AS count ';
			for ($i = 1; $i < count($split); $i++) {
				$listQuery .= sprintf(' FROM %s', $split[$i]);
			}
		}

		if ($this->getModule()->get('name') == 'Calendar') {
			$listQuery .= ' && activitytype <> "Emails"';
		}
		$listResult = $db->query($listQuery);
		return $db->getSingleValue($listResult);
	}
}
