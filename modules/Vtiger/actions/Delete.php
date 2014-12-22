<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Delete_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPrivilegesModel->isPermitted($moduleName, 'Delete', $record)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$ajaxDelete = $request->get('ajaxDelete');
		
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$moduleModel = $recordModel->getModule();

		$recordModel->delete();

		$listViewUrl = $moduleModel->getListViewUrl();
		if($ajaxDelete) {
			$response = new Vtiger_Response();
			$response->setResult($listViewUrl);
			return $response;
		} else {
			header("Location: $listViewUrl");
		}
	}
        
        public function validateRequest(Vtiger_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}
