<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Services_List_View extends Vtiger_List_View
{
	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$modulePopUpFile = 'modules.' . $moduleName . '.resources.Edit';
		unset($headerScriptInstances[$modulePopUpFile]);
		return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts([
			'modules.Products.resources.Edit', $modulePopUpFile
		]));
	}
}
