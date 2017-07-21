<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Project_Detail_View extends Vtiger_Detail_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showCharts');
		$this->exposeMethod('showGantt');
	}

	public function showCharts(\App\Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$moduleModel = Vtiger_Module_Model::getInstance('OSSTimeControl');
		if ($moduleModel) {
			$data = $moduleModel->getTimeUsers($recordId, $moduleName);
		}
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->view('charts/ShowTimeProjectUsers.tpl', $moduleName);
	}

	public function showGantt(\App\Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getGanttProject($recordId);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', \App\Json::encode($data));
		$viewer->view('gantt/GanttContents.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param \App\Request $request
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$cssFileNames = array(
			'~libraries/gantt/skins/dhtmlxgantt_broadway.css',
			'~libraries/jquery/flot/jquery.flot.valuelabels.css',
		);
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles($cssFileNames));
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param \App\Request $request
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$jsFileNames = array(
			'~libraries/gantt/dhtmlxgantt.js',
			'~libraries/jquery/flot/jquery.flot.min.js',
			'~libraries/jquery/flot/jquery.flot.resize.js',
			'~libraries/jquery/flot/jquery.flot.stack.min.js',
			'~libraries/jquery/flot/jquery.flot.valuelabels.min.js',
		);
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}
}
