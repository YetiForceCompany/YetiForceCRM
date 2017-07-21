<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class HelpDesk_Detail_View extends Vtiger_Detail_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showCharts');
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param \App\Request $request
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$jsFileNames = array(
			'~libraries/jquery/flot/jquery.flot.min.js',
			'~libraries/jquery/flot/jquery.flot.resize.js',
			'~libraries/jquery/flot/jquery.flot.stack.min.js',
			'~libraries/jquery/flot/jquery.flot.valuelabels.min.js',
		);
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param \App\Request $request
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$cssFileNames = array(
			'~libraries/jquery/flot/jquery.flot.valuelabels.css',
		);
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles($cssFileNames));
	}

	public function showCharts(\App\Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$moduleModel = Vtiger_Module_Model::getInstance('OSSTimeControl');
		if ($moduleModel)
			$data = $moduleModel->getTimeUsers($recordId, $moduleName);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->view('charts/ShowTimeHelpDesk.tpl', $moduleName);
	}
}
