<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_PickListDependency_List_View extends Settings_Vtiger_List_View
{
	public function preProcess(\App\Request $request, $display = true)
	{
		$moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();
		$forModule = $request->get('formodule');
		$viewer = $this->getViewer($request);
		$viewer->assign('PICKLIST_MODULES_LIST', $moduleModelList);
		$viewer->assign('FOR_MODULE', $forModule);
		parent::preProcess($request, $display);
	}

	public function process(\App\Request $request)
	{
		if ($request->isAjax()) {
			$moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();
			$forModule = $request->get('formodule');

			$viewer = $this->getViewer($request);
			$viewer->assign('PICKLIST_MODULES_LIST', $moduleModelList);
			$viewer->assign('FOR_MODULE', $forModule);

			$viewer = $this->getViewer($request);
			$this->initializeListViewContents($request, $viewer);
			$viewer->view('ListViewHeader.tpl', $request->getModule(false));
		}
		parent::process($request);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$jsFileNames = [
			'~libraries/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js',
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}

	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = [
			'~libraries/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}
