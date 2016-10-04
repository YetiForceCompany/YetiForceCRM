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
 * PBXManager ListView Model Class
 */
class PBXManager_ListView_Model extends Vtiger_ListView_Model
{

	/**
	 * Overrided to remove add button 
	 */
	public function getBasicLinks()
	{
		$basicLinks = array();
		return $basicLinks;
	}

	/**
	 * Overrided to remove Mass Edit Option 
	 */
	public function getListViewMassActions($linkParams)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWMASSACTION');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);


		if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleModel->get('name') . '&action=MassDelete");',
				'linkicon' => ''
			);

			foreach ($massActionLinks as $massActionLink) {
				$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
			}
		}

		return $links;
	}

	/**
	 * Overrided to add HTML content for callstatus irrespective of the filters
	 */
	public function getListViewEntries($pagingModel, $searchResult = false)
	{
		$db = PearDatabase::getInstance();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		//Add the direction field to the query irrespective of filter
		$queryGenerator = $this->get('query_generator');
		$fields = $queryGenerator->getFields();
		array_push($fields, 'direction');
		$queryGenerator->setFields($fields);
		$this->set('query_generator', $queryGenerator);
		//END

		$listViewContoller = $this->get('listview_controller');

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
		$listQuery = $this->getQuery();

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
			if ($orderByFieldModel->isReferenceField()) {
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
		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

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

		//Adding the HTML content based on the callstatus and direction to the records
		foreach ($listViewEntries as $recordId => $record) {
			//To Replace RecordingUrl by Icon
			$recordingUrl = explode('>', $listViewEntries[$recordId]['recordingurl']);
			$url = explode('<', $recordingUrl[1]);
			if ($url[0] != '' && $listViewEntries[$recordId]['callstatus'] == 'completed') {
				$listViewEntries[$recordId]['recordingurl'] = $recordingUrl[0] . '>' . '<i class="icon-volume-up"></i>' . '</a>';
			} else {
				$listViewEntries[$recordId]['recordingurl'] = '';
			}


			if ($listViewEntries[$recordId]['direction'] == 'outbound') {
				if ($listViewEntries[$recordId]['callstatus'] == 'ringing' || $listViewEntries[$recordId]['callstatus'] == 'in-progress') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-info"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else if ($listViewEntries[$recordId]['callstatus'] == 'completed') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-success"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else if ($listViewEntries[$recordId]['callstatus'] == 'no-answer') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-important"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-warning"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				}
			} else if ($listViewEntries[$recordId]['direction'] == 'inbound') {
				if ($listViewEntries[$recordId]['callstatus'] == 'ringing' || $listViewEntries[$recordId]['callstatus'] == 'in-progress') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-info"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else if ($listViewEntries[$recordId]['callstatus'] == 'completed') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-success"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else if ($listViewEntries[$recordId]['callstatus'] == 'no-answer') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-important"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-warning"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				}
			}
		}
		//END

		$index = 0;
		foreach ($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
			$listViewRecordModels[$recordId]->colorList = Settings_DataAccess_Module_Model::executeColorListHandlers($moduleName, $recordId, $moduleModel->getRecordFromArray($listViewContoller->rawData[$recordId]));
		}

		return $listViewRecordModels;
	}
}
