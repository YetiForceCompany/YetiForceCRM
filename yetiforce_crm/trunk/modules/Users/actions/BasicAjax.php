<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Users_BasicAjax_Action extends Vtiger_BasicAjax_Action {

	function checkPermission(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if(!$currentUser->isAdminUser()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
		}
	}

	public function process(Vtiger_Request $request) {
		$searchValue = $request->get('search_value');
		$searchModule = $request->get('search_module');

		$parentRecordId = $request->get('parent_id');
		$parentModuleName = $request->get('parent_module');

		$searchModuleModel = Users_Module_Model::getInstance($searchModule);
		$records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName);

		$result = array();
		if(is_array($records)){
			foreach($records as $moduleName=>$recordModels) {
				foreach($recordModels as $recordModel) {
					$result[] = array('label'=>decode_html($recordModel->getName()), 'value'=>decode_html($recordModel->getName()), 'id'=>$recordModel->getId());
				}
			}
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
