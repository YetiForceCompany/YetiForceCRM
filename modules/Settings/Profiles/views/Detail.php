<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Profiles_Detail_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_Profiles_Record_Model::getInstanceById($recordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('ALL_BASIC_ACTIONS', Vtiger_Action_Model::getAllBasic(true));
		$viewer->assign('ALL_UTILITY_ACTIONS', Vtiger_Action_Model::getAllUtility(true));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->view('DetailView.tpl', $qualifiedModuleName);
	}

}
