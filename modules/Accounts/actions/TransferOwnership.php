<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Accounts_TransferOwnership_Action extends Vtiger_Action_Controller {
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request) {
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$transferOwnerId = $request->get('transferOwnerId');
		$record = $request->get('record');
		if(empty($record))
			$recordIds = $this->getBaseModuleRecordIds($request);
		else
			$recordIds[] = $record;
		$relatedModuleRecordIds = $moduleModel->getRelatedModuleRecordIds($request, $recordIds);
		foreach ($recordIds as $key => $recordId) {
			array_push($relatedModuleRecordIds, $recordId);
		}
		array_merge($relatedModuleRecordIds, $recordIds);
		$moduleModel->transferRecordsOwnership($transferOwnerId, $relatedModuleRecordIds);
		
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
	
	protected function getBaseModuleRecordIds(Vtiger_Request $request) {
		$cvId = $request->get('viewname');
		$module = $request->getModule();
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}

		if($selectedIds == 'all'){
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
			if($customViewModel) {
				return $customViewModel->getRecordIds($excludedIds, $module);
			}
		}
        return array();
	}
    
    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }
}
