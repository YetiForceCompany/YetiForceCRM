<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Products_ListView_Model extends Vtiger_ListView_Model
{

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel, $skipSelected = false)
	{
		$db = PearDatabase::getInstance();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');

		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}

		$glue = "";
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

		//List view will be displayed on recently created/modified records
		if (empty($orderBy) && empty($sortOrder) && $moduleName != "Users") {
			$orderBy = 'modifiedtime';
			$sortOrder = 'DESC';
		}

		if (!empty($orderBy)) {
			$columnFieldMapping = $moduleModel->getColumnFieldMapping();
			$orderByFieldName = $columnFieldMapping[$orderBy];
			$orderByFieldModel = $moduleModel->getField($orderByFieldName);
			if ($orderByFieldModel && $orderByFieldModel->isReferenceField()) {
				//IF it is reference add it in the where fields so that from clause will be having join of the table
				$queryGenerator = $this->get('query_generator');
				$queryGenerator->setConditionField($orderByFieldName);
			}
		}

		if (!empty($orderBy) && $orderBy === 'smownerid') {
			$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
			if ($fieldModel->getFieldDataType() == 'owner') {
				$orderBy = 'COALESCE(' . \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . ',vtiger_groups.groupname)';
			}
		}

		$listQuery = $this->getQuery();

		// Limit the choice of products/services only to the ones related to currently selected Opportunity - last step.
		if (Settings_SalesProcesses_Module_Model::checkRelatedToPotentialsLimit($this->get('src_module'))) {
			$salesProcessId = $this->get('salesprocessid');
			if (empty($salesProcessId)) {
				$salesProcessId = -1;
			}
			$newListQuery = '';
			$explodedListQuery = explode('INNER JOIN', $listQuery);
			foreach ($explodedListQuery as $key => $value) {
				$newListQuery .= 'INNER JOIN' . $value;
				if ($key == 0 && $moduleName == 'Products') {
					$newListQuery .= ' INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_products.productid || vtiger_crmentityrel.crmid = vtiger_products.productid) ';
				} elseif ($key == 0 && $moduleName == 'Services') {
					$newListQuery .= ' INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_service.serviceid || vtiger_crmentityrel.crmid = vtiger_service.serviceid) ';
				}
			}
			$newListQuery = trim($newListQuery, 'INNER JOIN');
			if (in_array($moduleName, ['Products', 'Services'])) {
				$newListQuery .= " && ( (vtiger_crmentityrel.crmid = '$salesProcessId' && module = 'SSalesProcesses') || (vtiger_crmentityrel.relcrmid = '$salesProcessId' && relmodule = 'SSalesProcesses')) ";
			}
			$listQuery = $newListQuery;
		}

		if ($this->get('subProductsPopup')) {
			$listQuery = $this->addSubProductsQuery($listQuery);
		}
		$sourceModule = $this->get('src_module');
		$sourceField = $this->get('src_field');
		if (!empty($sourceModule)) {
			if (method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $sourceField, $this->get('src_record'), $listQuery, $skipSelected);
				if (!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		if (!empty($orderBy)) {
			if ($orderByFieldModel && $orderByFieldModel->isReferenceField()) {
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
						$referenceNameFieldOrderBy[] = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => $columnList[0], 'last_name' => $columnList[1]), 'Users') . ' ' . $sortOrder;
					} else {
						$referenceNameFieldOrderBy[] = implode('', $columnList) . ' ' . $sortOrder;
					}
				}
				$listQuery .= sprintf(' ORDER BY %s', implode(',', $referenceNameFieldOrderBy));
			} else {
				$listQuery .= sprintf(' ORDER BY %s %s', $orderBy, $sortOrder);
			}
		}

		$viewid = ListViewSession::getCurrentView($moduleName);
		if (empty($viewid)) {
			$viewid = $pagingModel->get('viewid');
		}
		$_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');
		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		//For Products popup in Price Book Related list
		if ($sourceModule !== 'PriceBooks' && $sourceField !== 'priceBookRelatedList') {
			$listQuery .= " LIMIT $startIndex," . ($pageLimit + 1);
		}

		$listResult = $db->pquery($listQuery, []);

		$listViewRecordModels = [];
		$listViewEntries = $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);
		$pagingModel->calculatePageRange($listViewEntries);

		if ($db->num_rows($listResult) > $pageLimit && $sourceModule !== 'PriceBooks' && $sourceField !== 'priceBookRelatedList') {
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		$index = 0;
		foreach ($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
			$listViewRecordModels[$recordId]->colorList = Settings_DataAccess_Module_Model::executeColorListHandlers($moduleName, $recordId, $moduleModel->getRecordFromArray($listViewContoller->rawData[$recordId]));
		}
		return $listViewRecordModels;
	}

	public function addSubProductsQuery($listQuery)
	{
		$splitQuery = preg_split('/WHERE/i', $listQuery, 2);
		$query = " LEFT JOIN vtiger_seproductsrel ON vtiger_seproductsrel.crmid = vtiger_products.productid && vtiger_seproductsrel.setype='Products'";
		$splitQuery[0] .= $query;
		$productId = $this->get('productId');
		$query1 = " && vtiger_seproductsrel.productid = $productId";
		$splitQuery[1] .= $query1;
		$listQuery = $splitQuery[0] . ' WHERE ' . $splitQuery[1];
		return $listQuery;
	}

	public function getSubProducts($subProductId)
	{
		$flag = false;
		if (!empty($subProductId)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT vtiger_seproductsrel.crmid from vtiger_seproductsrel INNER JOIN
                vtiger_crmentity ON vtiger_seproductsrel.crmid = vtiger_crmentity.crmid 
					AND vtiger_crmentity.deleted = 0 && vtiger_seproductsrel.setype=? 
				WHERE vtiger_seproductsrel.productid=?", array($this->getModule()->get('name'), $subProductId));
			if ($db->num_rows($result) > 0) {
				$flag = true;
			}
		}
		return $flag;
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

		if ($this->get('subProductsPopup')) {
			$listQuery = $this->addSubProductsQuery($listQuery);
		}

		$sourceModule = $this->get('src_module');
		if (!empty($sourceModule)) {
			$moduleModel = $this->getModule();
			if (method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
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
