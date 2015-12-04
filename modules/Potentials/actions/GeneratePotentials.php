<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Potentials_GeneratePotentials_Action extends Vtiger_Action_Controller {
	
	public function checkPermission(Vtiger_Request $request) {
		$recordPermission = Users_Privileges_Model::isPermitted('Potentials', 'EditView');
		if(!$recordPermission) {
			throw new NoPermittedException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request) {
		$module = $request->getModule();
		$recordModel = Vtiger_Record_Model::getCleanInstance($module);
		$from_module = $request->get('from_module');
		$recordIds = $this->getBaseModuleRecordIds($request);
		$count = $recordModel->createSalesOpportunitiesFromRecords($from_module, $recordIds);
		$response = new Vtiger_Response();
		$response->setResult( vtranslate('LBL_GENERATED_OPPORTUNITIES_COMPLETED', $module).' '.sprintf( vtranslate('LBL_GENERATED_OPPORTUNITIES_INFO', $module) , $count ) );
		$response->emit();
	}

	protected function getBaseModuleRecordIds(Vtiger_Request $request) {
		$cvId = $request->get('viewname');
		$module = $request->get('from_module');
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
}
