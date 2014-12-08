<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_FindDuplicates_View extends Vtiger_List_View {

	function preProcess(Vtiger_Request $request, $display = true) {
		$viewer = $this->getViewer ($request);
		$this->initializeListViewContents($request, $viewer);
		parent::preProcess($request, $display);
	}

	public function preProcessTplName(Vtiger_Request $request) {
		return 'FindDuplicatePreProcess.tpl';
	}

	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$this->initializeListViewContents($request, $viewer);

		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('FindDuplicateContents.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.List',
			'modules.Vtiger.resources.FindDuplicates',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$currentUser = vglobal('current_user');
		$viewer = $this->getViewer ($request);
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);

		$massActionLink = array(
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_DELETE',
			'linkurl' => 'Javascript:Vtiger_FindDuplicates_Js.massDeleteRecords("index.php?module='.$module.'&action=MassDelete");',
			'linkicon' => ''
		);
		$massActionLinks[] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		$viewer->assign('LISTVIEW_LINKS', $massActionLinks);
		$viewer->assign('MODULE_MODEL', $moduleModel);

		$pageNumber = $request->get('page');
		if(empty($pageNumber)){
			$pageNumber = '1';
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pageLimit = $pagingModel->getPageLimit();

		$duplicateSearchFields = $request->get('fields');
		$dataModelInstance = Vtiger_FindDuplicate_Model::getInstance($module);
		$dataModelInstance->set('fields', $duplicateSearchFields);

		$ignoreEmpty = $request->get('ignoreEmpty');
		$ignoreEmptyValue = false;
		if($ignoreEmpty == 'on' || $ignoreEmpty == 'true' || $ignoreEmpty == '1') $ignoreEmptyValue = true;
		$dataModelInstance->set('ignoreEmpty', $ignoreEmptyValue);

		if(!$this->listViewEntries) {
			$this->listViewEntries = $dataModelInstance->getListViewEntries($pagingModel);
		}

		if(!$this->listViewHeaders){
			$this->listViewHeaders = $dataModelInstance->getListViewHeaders();
		}
		if(!$this->rows) {
			$this->rows = $dataModelInstance->getRecordCount();
			$viewer->assign('TOTAL_COUNT', $this->rows);
		}

		$rowCount = 0;
		foreach($this->listViewEntries as $group) {
			foreach($group as $row) {
				$rowCount++;
			}
		}
		//for calculating the page range
		for($i=0; $i<$rowCount; $i++) $dummyListEntries[] = $i;
		$pagingModel->calculatePageRange($dummyListEntries);

		$viewer->assign('IGNORE_EMPTY', $ignoreEmpty);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $rowCount);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER',$pageNumber);
		$viewer->assign('MODULE', $module);
		$viewer->assign('DUPLICATE_SEARCH_FIELDS', $duplicateSearchFields);

		$customViewModel = CustomView_Record_Model::getAllFilterByModule($module);
		$viewer->assign('VIEW_NAME', $customViewModel->getId());
	}

	/**
	 * Function returns the number of records for the current filter
	 * @param Vtiger_Request $request
	 */
	function getRecordsCount(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$duplicateSearchFields = $request->get('fields');
		$dataModelInstance = Vtiger_FindDuplicate_Model::getInstance($moduleName);

		$ignoreEmpty = $request->get('ignoreEmpty');
		$ignoreEmptyValue = false;
		if($ignoreEmpty == 'on' || $ignoreEmpty == 'true' || $ignoreEmpty == '1') $ignoreEmptyValue = true;
		$dataModelInstance->set('ignoreEmpty', $ignoreEmptyValue);

		$dataModelInstance->set('fields', $duplicateSearchFields);
		$count = $dataModelInstance->getRecordCount();

		$result = array();
		$result['module'] = $moduleName;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}