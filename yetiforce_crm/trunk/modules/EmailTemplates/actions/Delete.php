<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class EmailTemplates_Delete_Action extends Vtiger_Delete_Action {
	
	function checkPermission(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$ajaxDelete = $request->get('ajaxDelete');
		
		$recordModel = EmailTemplates_Record_Model::getInstanceById($recordId);
		$moduleModel = $recordModel->getModule();

		$recordModel->delete($recordId);

		$listViewUrl = $moduleModel->getListViewUrl();
		if($ajaxDelete) {
			$response = new Vtiger_Response();
			$response->setResult($listViewUrl);
			return $response;
		} else {
			header("Location: $listViewUrl");
		}
	}
}
