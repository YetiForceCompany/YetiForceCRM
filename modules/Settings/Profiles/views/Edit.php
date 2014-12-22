<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Profiles_Edit_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
        $this->initialize($request);
        $qualifiedModuleName = $request->getModule(false);
        
        $viewer = $this->getViewer($request);
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}
    
    public function initialize(Vtiger_Request $request) {
        $viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->get('record');
		$fromRecord = $request->get('from_record');

		if(!empty($record)) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} elseif(!empty($fromRecord)) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($fromRecord);
			$recordModel->getModulePermissions();
            $recordModel->getGlobalPermissions();
			$recordModel->set('profileid', '');
			$viewer->assign('MODE', '');
			$viewer->assign('IS_DUPLICATE_RECORD',$fromRecord);
		} else {
			$recordModel = new Settings_Profiles_Record_Model();
			$viewer->assign('MODE', '');
		}
		$viewer->assign('ALL_PROFILES',$recordModel->getAll());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ALL_BASIC_ACTIONS', Vtiger_Action_Model::getAllBasic(true));
		$viewer->assign('ALL_UTILITY_ACTIONS', Vtiger_Action_Model::getAllUtility(true)); 
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
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
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.$moduleName.resources.Edit"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}