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
	public function getListViewEntries($pagingModel)
	{
		$db = PearDatabase::getInstance();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');

		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = array();
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
			if ($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
				//IF it is reference add it in the where fields so that from clause will be having join of the table
				$queryGenerator = $this->get('query_generator');
				$queryGenerator->addWhereField($orderByFieldName);
			}
		}

		if (!empty($orderBy) && $orderBy === 'smownerid') {
			$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
			if ($fieldModel->getFieldDataType() == 'owner') {
				$orderBy = 'COALESCE(' . getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . ',vtiger_groups.groupname)';
			}
		}

		$listQuery = $this->getQuery();
		$request = new Vtiger_Request($_REQUEST, $_REQUEST);
		$potential_id = $this->get('potential_id');
		if (Settings_SalesProcesses_Module_Model::checkRelatedToPotentialsLimit() && Settings_SalesProcesses_Module_Model::isLimitForModule($request->get('module'))) {
			if (empty($potential_id)) {
				$potential_id = $this->get('potentialid');
				if ($potential_id == '') {
					$potential_id = -1;
				}
			}
			$newListQuery = '';
			$explodedListQuery = explode('INNER JOIN', $listQuery);
			foreach ($explodedListQuery as $key => $value) {
				$newListQuery .= 'INNER JOIN' . $value;
				if ($key == 0 && $moduleName == 'Products') {
					$newListQuery .= ' INNER JOIN vtiger_seproductsrel AS seproductsrel ON vtiger_products.productid = seproductsrel.productid ';
				} elseif ($key == 0 && $moduleName == 'Services') {
					$newListQuery .= ' INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_service.serviceid OR vtiger_crmentityrel.crmid = vtiger_service.serviceid) ';
				}
			}
			$newListQuery = trim($newListQuery, 'INNER JOIN');
			if ($moduleName == 'Products') {
				$newListQuery .= " AND seproductsrel.crmid = '$potential_id' ";
			} elseif ($moduleName == 'Services') {
				$newListQuery .= " AND ( (vtiger_crmentityrel.crmid = '$potential_id' AND module = 'Potentials') OR (vtiger_crmentityrel.relcrmid = '$potential_id' AND relmodule = 'Potentials')) ";
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
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $sourceField, $this->get('src_record'), $listQuery);
				if (!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		if (!empty($orderBy)) {
			if ($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
				$referenceModules = $orderByFieldModel->getReferenceList();
				$referenceNameFieldOrderBy = array();
				foreach ($referenceModules as $referenceModuleName) {
					$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModuleName);
					$referenceNameFields = $referenceModuleModel->getNameFields();

					$columnList = array();
					foreach ($referenceNameFields as $nameField) {
						$fieldModel = $referenceModuleModel->getField($nameField);
						$columnList[] = $fieldModel->get('table') . $orderByFieldModel->getName() . '.' . $fieldModel->get('column');
					}
					if (count($columnList) > 1) {
						$referenceNameFieldOrderBy[] = getSqlForNameInDisplayFormat(array('first_name' => $columnList[0], 'last_name' => $columnList[1]), 'Users') . ' ' . $sortOrder;
					} else {
						$referenceNameFieldOrderBy[] = implode('', $columnList) . ' ' . $sortOrder;
					}
				}
				$listQuery .= ' ORDER BY ' . implode(',', $referenceNameFieldOrderBy);
			} else {
				$listQuery .= ' ORDER BY ' . $orderBy . ' ' . $sortOrder;
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

		$listResult = $db->pquery($listQuery, array());

		$listViewRecordModels = array();
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
			$listViewRecordModels[$recordId]->lockEditView = Users_Privileges_Model::checkLockEdit($moduleName, $recordId);
			$listViewRecordModels[$recordId]->isPermittedToEditView = Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId);
			$listViewRecordModels[$recordId]->colorList = Settings_DataAccess_Module_Model::executeColorListHandlers($moduleName, $recordId, $listViewRecordModels[$recordId]);
		}
		return $listViewRecordModels;
	}

	public function addSubProductsQuery($listQuery)
	{
		$splitQuery = split('WHERE', $listQuery);
		$query = " LEFT JOIN vtiger_seproductsrel ON vtiger_seproductsrel.crmid = vtiger_products.productid AND vtiger_seproductsrel.setype='Products'";
		$splitQuery[0] .= $query;
		$productId = $this->get('productId');
		$query1 = " AND vtiger_seproductsrel.productid = $productId";
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
					AND vtiger_crmentity.deleted = 0 AND vtiger_seproductsrel.setype=? 
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
			$searchParams = array();
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
			$split = explode(' from ', $listQuery);
			$splitCount = count($split);
			$listQuery = 'SELECT count(*) AS count ';
			for ($i = 1; $i < $splitCount; $i++) {
				$listQuery = $listQuery . ' FROM ' . $split[$i];
			}
		}

		if ($this->getModule()->get('name') == 'Calendar') {
			$listQuery .= ' AND activitytype <> "Emails"';
		}

		$listResult = $db->pquery($listQuery, array());
		return $db->query_result($listResult, 0, 'count');
	}
}
