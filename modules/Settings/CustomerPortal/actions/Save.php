<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_CustomerPortal_Save_Action extends Settings_Vtiger_Index_Action {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$privileges = $request->get('privileges');
		$defaultAssignee = $request->get('defaultAssignee');
		$portalModulesInfo = $request->get('portalModulesInfo');

		if ($privileges && $defaultAssignee && $portalModulesInfo) {
			$moduleModel = Settings_CustomerPortal_Module_Model::getInstance($qualifiedModuleName);
			$moduleModel->set('privileges', $privileges);
			$moduleModel->set('defaultAssignee', $defaultAssignee);
			$moduleModel->set('portalModulesInfo', $portalModulesInfo);
			$moduleModel->save();
		}
		
		$responce = new Vtiger_Response();
        $responce->setResult(array('success'=>true));
        $responce->emit();
	}
        
        public function validateRequest(Vtiger_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}