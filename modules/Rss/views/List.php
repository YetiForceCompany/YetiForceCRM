<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Rss_List_View extends Vtiger_Index_View
{
	public function preProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('HEADER_LINKS', ['LIST_VIEW_HEADER' => []]);
		parent::preProcess($request);
	}

	public function preProcessTplName(App\Request $request)
	{
		return 'ListViewPreProcess.tpl';
	}

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('SOURCE_MODULE', $moduleName);
		$viewer->view('ListViewContents.tpl', $moduleName);
	}

	public function postProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->view('ListViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	// Function to initialize the required data in smarty to display the List View Contents

	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer)
	{
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		if (!$request->isEmpty('id')) {
			$recordInstance = Rss_Record_Model::getInstanceById($request->getInteger('id'), $module);
		} else {
			$recordInstance = Rss_Record_Model::getCleanInstance($module);
			$recordInstance->getDefaultRss();
			$recordInstance = Rss_Record_Model::getInstanceById($recordInstance->getId(), $module);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $module);
		$viewer->assign('RECORD', $recordInstance);
		$linkParams = ['MODULE' => $module, 'ACTION' => $request->getByType('view', 1)];
		$viewer->assign('QUICK_LINKS', $moduleModel->getSideBarLinks($linkParams));
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
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.List',
			"modules.$moduleName.resources.List",
			'modules.CustomView.resources.CustomView',
			"modules.$moduleName.resources.CustomView",
			'modules.Vtiger.resources.ListSearch',
			"modules.{$moduleName}.resources.ListSearch"
		]));
	}
}
