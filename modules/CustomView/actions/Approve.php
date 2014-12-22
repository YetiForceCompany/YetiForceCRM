<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class CustomView_Approve_Action extends Vtiger_Action_Controller {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if($currentUser->isAdminUser()) {
		$customViewModel = CustomView_Record_Model::getInstanceById($request->get('record'));
		$moduleModel = $customViewModel->getModule();

		$customViewModel->approve();
		}
		$listViewUrl = $moduleModel->getListViewUrl();
		header("Location: $listViewUrl");
	}
        
         public function validateRequest(Vtiger_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}
