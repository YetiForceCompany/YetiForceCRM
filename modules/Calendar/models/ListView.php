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
		$basicLinks = [];
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView');
		if ($createPermission) {
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_EVENT',
				'linkurl' => $this->getModule()->getCreateEventRecordUrl(),
				'linkclass' => 'moduleColor_' . $moduleModel->getName(),
				'linkicon' => '',
				'showLabel' => 1,
			];
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_TASK',
				'linkurl' => $this->getModule()->getCreateTaskRecordUrl(),
				'linkclass' => 'moduleColor_' . $moduleModel->getName(),
				'linkicon' => '',
				'showLabel' => 1,
			];
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
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView') && Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = [];
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
	public function getQuery()
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
		$moduleModel = $this->getModule();
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['LISTVIEWMASSACTION'], $linkParams);

		$massActionLinks = [];
		if ($moduleModel->isPermitted('MassTransferOwnership')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_CHANGE_OWNER',
				'linkurl' => 'javascript:Calendar_List_Js.triggerMassEdit("index.php?module=' . $moduleModel->get('name') . '&view=MassActionAjax&mode=showMassEditForm");',
				'linkicon' => ''
			);
		}
		if ($moduleModel->isPermitted('MassDelete')) {
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
		$headerFieldModels = [];
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
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');

		$listViewFields = array('visibility', 'assigned_user_id', 'activitystatus');
		$queryGenerator->setFields(array_unique(array_merge($queryGenerator->getFields(), $listViewFields)));

		$this->loadListViewCondition($moduleName);
		$listOrder = $this->getListViewOrderBy();

		$listQuery = $this->getQuery();
		if ($searchResult && $searchResult != '' && is_array($searchResult)) {
			$listQuery .= ' && vtiger_crmentity.crmid IN (' . implode(',', $searchResult) . ') ';
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

		$listQuery .= $listOrder;
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$viewid = ListViewSession::getCurrentView($moduleName);
		if (empty($viewid)) {
			$viewid = $pagingModel->get('viewid');
		}
		$_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');
		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		$listQuery .= " LIMIT $startIndex," . ($pageLimit + 1);

		$listResult = $db->query($listQuery);

		$listViewRecordModels = [];
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

			if ($orderBy == 'date_start') {
				$orderBy = "str_to_date(concat(date_start,time_start),'%Y-%m-%d %H:%i:%s')";
			} else if ($orderBy == 'due_date') {
				$orderBy = "str_to_date(concat(due_date,time_end),'%Y-%m-%d %H:%i:%s')";
			}

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
				$query = ' ORDER BY %s';
				$query = sprintf($query, implode(',', $referenceNameFieldOrderBy));
			} else if ($orderBy === 'smownerid') {
				$this->get('query_generator')->setConditionField($orderByFieldName);
				$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
				if ($fieldModel->getFieldDataType() == 'owner') {
					$orderBy = 'COALESCE(' . \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . ',vtiger_groups.groupname)';
				}
				$query = ' ORDER BY %s %s';
				$query = sprintf($query, $orderBy, $sortOrder);
			} else {
				$query = ' ORDER BY %s %s';
				$query = sprintf($query, $orderBy, $sortOrder);
			}
		}
		return $query;
	}
}
