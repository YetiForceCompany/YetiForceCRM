<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Campaigns_Detail_View extends Vtiger_Detail_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showCountRecords');
	}

	public function showCountRecords($request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$relatedModules = $request->get('relatedModules');
		$relatedModulesNames = [];
		foreach ($relatedModules as $tabId) {
			$relatedModulesNames[$tabId] = vtlib\Functions::getModuleName($tabId);
		}
		$countRecords = Vtiger_CountRecords_Widget::getCountRecords($relatedModulesNames, $recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('COUNT_RECORDS', $countRecords);
		$viewer->assign('RELATED_MODULES', $relatedModulesNames);
		return $viewer->view('CountRecordsContent.tpl', $moduleName, true);
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
			'modules.Vtiger.resources.List',
			"modules.$moduleName.resources.List",
			'modules.CustomView.resources.CustomView',
			"modules.$moduleName.resources.CustomView",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
