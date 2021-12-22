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
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showCountRecords');
	}

	/**
	 * Shows quantity of records.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function showCountRecords(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$relatedModules = $request->getByType('relatedModules');
		$relatedModulesNames = [];
		foreach ($relatedModules as $tabId) {
			$relatedModulesNames[$tabId] = \App\Module::getModuleName($tabId);
		}
		$countRecords = Vtiger_CountRecords_Widget::getCountRecords($relatedModulesNames, $recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('COUNT_RECORDS', $countRecords);
		$viewer->assign('RELATED_MODULES', $relatedModulesNames);

		return $viewer->view('CountRecordsContent.tpl', $moduleName, true);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();

		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.List',
			"modules.$moduleName.resources.List",
			'modules.CustomView.resources.CustomView',
			"modules.$moduleName.resources.CustomView",
		]));
	}
}
