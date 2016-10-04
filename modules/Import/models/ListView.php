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
class Import_ListView_Model extends Vtiger_ListView_Model
{

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return false - no List View Links needed on Import pages
	 */
	public function getListViewLinks($linkParams)
	{
		return false;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return false - no List View Links needed on Import pages
	 */
	public function getListViewMassActions($linkParams)
	{
		return false;
	}

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

		$listQuery = $queryGenerator->getQuery();

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$importedRecordIds = $this->getLastImportedRecord();
		$listViewRecordModels = array();
		if (count($importedRecordIds) != 0) {
			$moduleModel = $this->get('module');
			$listQuery .= ' && ' . $moduleModel->basetable . '.' . $moduleModel->basetableid . ' IN (' . implode(',', $importedRecordIds) . ')';

			$listQuery .= " LIMIT $startIndex, $pageLimit";

			$listResult = $db->pquery($listQuery, array());

			$listViewEntries = $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);
			$pagingModel->calculatePageRange($listViewEntries);
			foreach ($listViewEntries as $recordId => $record) {
				$record['id'] = $recordId;
				$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record);
			}
		}
		return $listViewRecordModels;
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

		$listQuery = $queryGenerator->getQuery();

		$importedRecordIds = $this->getLastImportedRecord();
		if (count($importedRecordIds) != 0) {
			$moduleModel = $this->get('module');
			$listQuery .= ' && ' . $moduleModel->basetable . '.' . $moduleModel->basetableid . ' IN (' . implode(',', $importedRecordIds) . ')';
		}

		$listResult = $db->pquery($listQuery, array());
		return $db->num_rows($listResult);
	}

	/**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param <String> $moduleName - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstance($moduleName, $viewId = '0')
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', 'Import');
		$instance = new $modelClassName();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);

		$customView = new CustomView();
		$viewId = $customView->getViewIdByName('All', $moduleName);
		$queryGenerator->initForCustomViewById($viewId);

		$controller = new ListViewController($db, $currentUser, $queryGenerator);

		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
	}

	public function getLastImportedRecord()
	{
		$db = PearDatabase::getInstance();

		$user = Users_Record_Model::getCurrentUserModel();
		$userDBTableName = Import_Utils_Helper::getDbTableName($user);
		$query = sprintf('SELECT recordid FROM %s WHERE temp_status NOT IN (?,?) && recordid IS NOT NULL', $userDBTableName);
		$result = $db->pquery($query, [Import_Data_Action::$IMPORT_RECORD_FAILED, Import_Data_Action::$IMPORT_RECORD_SKIPPED]);
		$noOfRecords = $db->num_rows($result);

		$importedRecordIds = array();
		for ($i = 0; $i < $noOfRecords; ++$i) {
			$importedRecordIds[] = $db->query_result($result, $i, 'recordid');
		}
		return $importedRecordIds;
	}
}
