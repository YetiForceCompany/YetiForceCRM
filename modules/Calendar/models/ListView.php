<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Vtiger ListView Model Class
 */
class Calendar_ListView_Model extends Vtiger_ListView_Model
{

	public function getBasicLinks()
	{
		$basicLinks = array();
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		if ($createPermission) {
			$basicLinks[] = array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_TASK',
				'linkurl' => $this->getModule()->getCreateTaskRecordUrl(),
				'linkicon' => ''
			);

			$basicLinks[] = array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_EVENT',
				'linkurl' => $this->getModule()->getCreateEventRecordUrl(),
				'linkicon' => ''
			);
		}
		return $basicLinks;
	}
	/*
	 * Function to give advance links of a module
	 * 	@RETURN array of advanced links
	 */

	public function getAdvancedLinks()
	{
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = array();
		$importPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Import');
		if ($importPermission && $createPermission) {
			$advancedLinks[] = array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_IMPORT',
				'linkurl' => 'javascript:Calendar_List_Js.triggerImportAction("' . $moduleModel->getImportUrl() . '")',
				'linkicon' => ''
			);
		}

		$exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
		if ($exportPermission) {
			$advancedLinks[] = array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_EXPORT',
				'linkurl' => 'javascript:Calendar_List_Js.triggerExportAction("' . $this->getModule()->getExportUrl() . '")',
				'linkicon' => ''
			);
		}
		return $advancedLinks;
	}

	/**
	 * Function to get query to get List of records in the current page
	 * @return <String> query
	 */
	function getQuery()
	{
		$queryGenerator = $this->get('query_generator');
		// Added to remove emails from the calendar list
		$queryGenerator->addCondition('activitytype', 'Emails', 'n', 'AND');

		$listQuery = $queryGenerator->getQuery();
		return $listQuery;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWMASSACTION');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);


		$massActionLinks = array();
		if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'MassTransferOwnership')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_CHANGE_OWNER',
				'linkurl' => 'javascript:Calendar_List_Js.triggerMassEdit("index.php?module=' . $moduleModel->get('name') . '&view=MassActionAjax&mode=showMassEditForm");',
				'linkicon' => ''
			);
		}
		if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'MassDelete')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleModel->get('name') . '&action=MassDelete");',
				'linkicon' => ''
			);
		}

		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders()
	{
		$listViewContoller = $this->get('listview_controller');
		$module = $this->getModule();
		$moduleName = $module->get('name');
		$headerFieldModels = array();
		$headerFields = $listViewContoller->getListViewHeaderFields();
		foreach ($headerFields as $fieldName => $webserviceField) {
			if ($webserviceField && !in_array($webserviceField->getPresence(), array(0, 2)))
				continue;
			$fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $module);
			if (!$fieldInstance) {
				if ($moduleName == 'Calendar') {
					$eventmodule = Vtiger_Module_Model::getInstance('Events');
					$fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $eventmodule);
				}
			}
			$headerFieldModels[$fieldName] = $fieldInstance;
		}
		return $headerFieldModels;
	}

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
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');
		$listViewFields = array('visibility', 'assigned_user_id', 'activitystatus');
		$queryGenerator->setFields(array_unique(array_merge($queryGenerator->getFields(), $listViewFields)));

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
				//$queryGenerator->whereFields[] = $orderByFieldName;
			}
		}

		if (!empty($orderBy) && $orderBy === 'smownerid') {
			$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
			if ($fieldModel->getFieldDataType() == 'owner') {
				$orderBy = 'COALESCE('.getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users', '').', vtiger_groups.groupname)';
			}
		}
		//To combine date and time fields for sorting
		if ($orderBy == 'date_start') {
			$orderBy = "str_to_date(concat(date_start,time_start),'%Y-%m-%d %H:%i:%s')";
		} else if ($orderBy == 'due_date') {
			$orderBy = "str_to_date(concat(due_date,time_end),'%Y-%m-%d %H:%i:%s')";
		}

		$listQuery = $this->getQuery();
		if ($searchResult && $searchResult != '' && is_array($searchResult)) {
			$listQuery .= " AND vtiger_crmentity.crmid IN (" . implode(', ', $searchResult) . ") ";
		}
		unset($searchResult);

		$sourceModule = $this->get('src_module');
		if (!empty($sourceModule)) {
			if (method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
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
				$listQuery .= ' ORDER BY ' . implode(', ', $referenceNameFieldOrderBy);
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

		$listQueryWithNoLimit = $listQuery;
		$listQuery .= " LIMIT $startIndex," . ($pageLimit + 1);

		$listResult = $db->pquery($listQuery, array());

		$listViewRecordModels = array();
		$listViewEntries = $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);

		$pagingModel->calculatePageRange($listViewEntries);

		if ($db->num_rows($listResult) > $pageLimit) {
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		$groupsIds = Vtiger_Util_Helper::getGroupsIdsForUsers($currentUser->getId());
		$index = 0;
		foreach ($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$visibleFields = array('activitytype', 'date_start', 'due_date', 'assigned_user_id', 'visibility', 'smownerid');
			$ownerId = $rawData['smownerid'];
			$visibility = true;
			if (in_array($ownerId, $groupsIds)) {
				$visibility = false;
			} else if ($ownerId == $currentUser->getId()) {
				$visibility = false;
			}

			if (!$currentUser->isAdminUser() && $rawData['activitytype'] != 'Task' && $rawData['visibility'] == 'Private' && $ownerId && $visibility) {
				foreach ($record as $data => $value) {
					if (in_array($data, $visibleFields) != -1) {
						unset($rawData[$data]);
						unset($record[$data]);
					}
				}
				$record['subject'] = vtranslate('Busy', 'Events') . '*';
			}
			if ($record['activitytype'] == 'Task') {
				unset($record['visibility']);
				unset($rawData['visibility']);
			}

			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
			$listViewRecordModels[$recordId]->lockEditView = Users_Privileges_Model::checkLockEdit($moduleName, $recordId);
			$listViewRecordModels[$recordId]->isPermittedToEditView = Users_Privileges_Model::isPermitted($moduleName, 'EditView  ', $recordId);
			$listViewRecordModels[$recordId]->colorList = Settings_DataAccess_Module_Model::executeColorListHandlers($moduleName, $recordId, $listViewRecordModels[$recordId]);
		}
		return $listViewRecordModels;
	}
}
