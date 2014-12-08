<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Export_View extends Vtiger_Index_View {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Export')) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		
		$source_module = $request->getModule();
		$viewId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$page = $request->get('page');

		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('VIEWID', $viewId);
		$viewer->assign('PAGE', $page);
		$viewer->assign('SOURCE_MODULE', $source_module);
		$viewer->assign('MODULE','Export');
        
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}
		$viewer->assign('SEARCH_PARAMS', $request->get('search_params'));
		$viewer->view('Export.tpl', $source_module);
	}
}