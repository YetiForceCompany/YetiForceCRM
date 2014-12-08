<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Profiles_Save_Action extends Vtiger_Action_Controller {
	
	public function checkPermission(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if(!$currentUser->isAdminUser()) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request) {
		$recordId = $request->get('record');

		if(!empty($recordId)) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = new Settings_Profiles_Record_Model();
		}
		if($recordModel) {
			$recordModel->set('profilename', $request->get('profilename'));
			$recordModel->set('description', $request->get('description'));
			$recordModel->set('viewall', $request->get('viewall'));
			$recordModel->set('editall', $request->get('editall'));
			$recordModel->set('profile_permissions', $request->get('permissions'));
			$recordModel->save();
		}
		
		$redirectUrl = $recordModel->getDetailViewUrl();
		header("Location: $redirectUrl");
	}
        
        public function validateRequest(Vtiger_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
