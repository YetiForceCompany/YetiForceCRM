<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Leads_MappingEdit_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE_MODEL', Settings_Leads_Mapping_Model::getInstance(TRUE));
		$viewer->assign('LEADS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Leads'));
		$viewer->assign('ACCOUNTS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Accounts'));
		$viewer->assign('CONTACTS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Contacts'));
		$viewer->assign('POTENTIALS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Potentials'));

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('LeadMappingEdit.tpl', $qualifiedModuleName);
	}
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.LeadMapping"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}