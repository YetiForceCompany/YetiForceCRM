<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_Leads_MappingEdit_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE_MODEL', Settings_Leads_Mapping_Model::getInstance(true));
		$viewer->assign('LEADS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Leads'));
		$viewer->assign('ACCOUNTS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Accounts'));

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('LeadMappingEdit.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.' . $request->getModule() . '.resources.LeadMapping',
		]));
	}
}
