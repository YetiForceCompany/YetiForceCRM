<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Groups_Save_Action extends Settings_Vtiger_Index_Action {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');

		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		if(!empty($recordId)) {
			$recordModel = Settings_Groups_Record_Model::getInstance($recordId);
		} else {
			$recordModel = new Settings_Groups_Record_Model();
		}
		if($recordModel) {
			$recordModel->set('groupname', decode_html($request->get('groupname')));
			$recordModel->set('description', $request->get('description'));
			$recordModel->set('group_members', $request->get('members'));
			$recordModel->save();
		}

		$redirectUrl = $recordModel->getDetailViewUrl();
		header("Location: $redirectUrl");
	}
        
        public function validateRequest(Vtiger_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}
