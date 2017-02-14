<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Services_QuickCreateAjax_View extends Products_QuickCreateAjax_View
{

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$jsFileNames = ['modules.Products.resources.Edit'];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($jsScriptInstances, $headerScriptInstances);
		return $headerScriptInstances;
	}
}
