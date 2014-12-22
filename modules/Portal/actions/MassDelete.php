<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Portal_MassDelete_Action extends Vtiger_MassDelete_Action {

    function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

    public function process(Vtiger_Request $request) {
        $module = $request->getModule();
        
        Portal_Module_Model::deleteRecords($request);
        
        $response = new Vtiger_Response();
        $result = array('message' => vtranslate('LBL_BOOKMARKS_DELETED_SUCCESSFULLY', $module));
        $response->setResult($result);
        $response->emit();
    }
}