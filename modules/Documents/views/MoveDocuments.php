<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Documents_MoveDocuments_View extends Vtiger_Index_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		if(!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	public function process (Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('FOLDERS', $moduleModel->getAllFolders());
		$viewer->assign('SELECTED_IDS', $request->get('selected_ids'));
		$viewer->assign('EXCLUDED_IDS', $request->get('excluded_ids'));
		$viewer->assign('VIEWNAME',$request->get('viewname'));
        
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
        if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
            $viewer->assign('SEARCH_KEY',$searchKey);
		}

		$viewer->view('MoveDocuments.tpl', $moduleName);
	}
}