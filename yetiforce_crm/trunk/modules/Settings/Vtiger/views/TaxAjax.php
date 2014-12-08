<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Vtiger_TaxAjax_View extends Settings_Vtiger_Index_View {
    
    public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$taxId = $request->get('taxid');
		$type = $request->get('type');
		
		if(empty($taxId)) {
            $taxRecordModel = new Settings_Vtiger_TaxRecord_Model();
        }else{
            $taxRecordModel = Settings_Vtiger_TaxRecord_Model::getInstanceById($taxId,$type);
        }
		
		$viewer->assign('TAX_TYPE', $type);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TAX_RECORD_MODEL', $taxRecordModel);

		echo $viewer->view('EditTax.tpl', $qualifiedModuleName, true);
    }
	
}