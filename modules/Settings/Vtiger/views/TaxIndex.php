<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_TaxIndex_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}

		$taxRecordModel = new Settings_Vtiger_TaxRecord_Model();
		$productAndServicesTaxList = Settings_Vtiger_TaxRecord_Model::getProductTaxes();

		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('TAX_RECORD_MODEL', $taxRecordModel);
		$viewer->assign('PRODUCT_AND_SERVICES_TAXES', $productAndServicesTaxList);
		$viewer->view('TaxIndex.tpl', $qualifiedModuleName);
	}

	public function getPageTitle(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_TAX_CALCULATIONS', $qualifiedModuleName);
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
			"modules.Settings.$moduleName.resources.Tax"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
