<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Rss_List_View extends Vtiger_Index_View
{
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function preProcessTplName(\App\Request $request)
	{
		return 'ListViewPreProcess.tpl';
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->view('ListViewContents.tpl', $moduleName);
	}

	public function postProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->view('ListViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	// Function to initialize the required data in smarty to display the List View Contents

	public function initializeListViewContents(\App\Request $request, Vtiger_Viewer $viewer)
	{
		$module = $request->getModule();
		$recordId = $request->get('id');
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		if ($recordId) {
			$recordInstance = Rss_Record_Model::getInstanceById($recordId, $module);
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
		$viewer->assign('LISTVIEW_HEADERS', $this->getListViewRssHeaders($module));
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
		$moduleName = $request->getModule();

		$jsFileNames = [
			'modules.Vtiger.resources.List',
			"modules.$moduleName.resources.List",
			'modules.CustomView.resources.CustomView',
			"modules.$moduleName.resources.CustomView",
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}

	/**
	 * Function to get the list view header.
	 *
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewRssHeaders($module)
	{
		$headerFields = [
			'title' => [
				'uitype' => '1',
				'name' => 'title',
				'label' => 'LBL_SUBJECT',
				'typeofdata' => 'V~O',
				'diplaytype' => '1',
			],
			'sender' => [
				'uitype' => '1',
				'name' => 'sender',
				'label' => 'LBL_SENDER',
				'typeofdata' => 'V~O',
				'diplaytype' => '1',
			],
		];
		foreach ($headerFields as $fieldName => $fieldDetails) {
			$fieldModel = new Vtiger_Field_Model();
			foreach ($fieldDetails as $name => $value) {
				$fieldModel->set($name, $value);
			}
			$fieldModel->module = $module;
			$fieldModelsList[$fieldName] = $fieldModel;
		}

		return $fieldModelsList;
	}
}
