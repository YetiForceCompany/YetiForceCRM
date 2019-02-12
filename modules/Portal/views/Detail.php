<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Portal_Detail_View extends Vtiger_Index_View
{
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
	}

	public function process(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$module = $request->getModule();

		$url = Portal_Module_Model::getWebsiteUrl($recordId);
		$recordList = Portal_Module_Model::getAllRecords();

		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE', $module);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('URL', $url);
		$viewer->assign('RECORDS_LIST', $recordList);

		$viewer->view('DetailView.tpl', $module);
	}

	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.List',
			'modules.Vtiger.resources.Detail',
			"modules.$moduleName.resources.List",
			"modules.$moduleName.resources.Detail",
		]));
	}
}
