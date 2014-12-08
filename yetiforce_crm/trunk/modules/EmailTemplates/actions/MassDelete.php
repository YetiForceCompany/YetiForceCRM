<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class EmailTemplates_MassDelete_Action extends Vtiger_Mass_Action {

	function checkPermission(){
		return true;
	}

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		$recordModel = new EmailTemplates_Record_Model();
		$recordModel->setModule($moduleName);
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		if($selectedIds == 'all' && empty($excludedIds)){
			$recordModel->deleteAllRecords();
		}else{
			$recordIds = $this->getRecordsListFromRequest($request, $recordModel);
			foreach($recordIds as $recordId) {
				$recordModel = EmailTemplates_Record_Model::getInstanceById($recordId);
				$recordModel->delete();
			}
		}
		
		$response = new Vtiger_Response();
		$response->setResult(array('module'=>$moduleName));
		$response->emit();
	}
	
	public function getRecordsListFromRequest(Vtiger_Request $request, $recordModel) {
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		
		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}
		if(!empty($excludedIds)){
			$moduleModel = $recordModel->getModule();
			$recordIds  = $moduleModel->getRecordIds($excludedIds);
			return $recordIds;
		}
	}
}
