<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_TermsAndConditionsEdit_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$model = Settings_Vtiger_TermsAndConditions_Model::getInstance();
		$conditionText = $model->getText();

		$viewer = $this->getViewer($request);
		$qualifiedName = $request->getModule(false);

		$viewer->assign('CONDITION_TEXT', $conditionText);
		$viewer->assign('MODEL', $model);
		$viewer->view('TermsAndConditions.tpl', $qualifiedName);
	}

	public function getPageTitle(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('INVENTORYTERMSANDCONDITIONS', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.TermsAndConditions"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
