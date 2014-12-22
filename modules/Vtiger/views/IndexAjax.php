<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_IndexAjax_View extends Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showActiveRecords');
	}

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/*
	 * Function to show the recently modified or active records for the given module
	 */
	function showActiveRecords(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recentRecords = $moduleModel->getRecentRecords();

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORDS', $recentRecords);

		echo $viewer->view('RecordNamesList.tpl', $moduleName, true);
	}

	function getRecordsListFromRequest(Vtiger_Request $request) {
		$cvId = $request->get('cvid');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}

		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if($customViewModel) {
            $searchKey = $request->get('search_key');
            $searchValue = $request->get('search_value');
            $operator = $request->get('operator');
            if(!empty($operator)) {
                $customViewModel->set('operator', $operator);
                $customViewModel->set('search_key', $searchKey);
                $customViewModel->set('search_value', $searchValue);
            }
			return $customViewModel->getRecordIds($excludedIds);
		}
	}
}