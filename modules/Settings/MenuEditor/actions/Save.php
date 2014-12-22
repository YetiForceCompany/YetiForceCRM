<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MenuEditor_Save_Action extends Settings_Vtiger_Index_Action {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		$menuEditorModuleModel = Settings_Vtiger_Module_Model::getInstance($moduleName);
		$selectedModulesList = $request->get('selectedModulesList');

		if ($selectedModulesList) {
			$menuEditorModuleModel->set('selectedModulesList', $selectedModulesList);
			$menuEditorModuleModel->saveMenuStruncture();
		}
		$loadUrl = $menuEditorModuleModel->getIndexViewUrl();
		header("Location: $loadUrl");
	}

        public function validateRequest(Vtiger_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
