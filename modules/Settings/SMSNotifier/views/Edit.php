<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_SMSNotifier_Edit_View extends Settings_Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		if ($recordId) {
			$recordModel = Settings_SMSNotifier_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		} else {
			$recordModel = Settings_SMSNotifier_Record_Model::getCleanInstance($qualifiedModuleName);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('EDITABLE_FIELDS', $recordModel->getEditableFields());
                $viewer->assign('PROVIDERS_FIELD_MODELS', Settings_SMSNotifier_ProviderField_Model::getAll()); 
		$viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('PROVIDERS', $recordModel->getModule()->getAllProviders());

		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}
}