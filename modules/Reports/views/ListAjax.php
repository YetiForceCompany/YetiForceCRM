<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
/*
 * Class for reloading the Reports headers
 */

class Reports_ListAjax_View extends Reports_List_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getListViewCount');
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getPageCount');
	}

	public function preProcess(Vtiger_Request $request)
	{
		
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$folders = $moduleModel->getFolders();
		$listViewModel = new Reports_ListView_Model();
		$listViewModel->set('module', $moduleModel);

		$linkModels = $listViewModel->getListViewLinks(false);

		$viewer->assign('LISTVIEW_LINKS', $linkModels);
		$viewer->assign('FOLDERS', $folders);

		$viewer->view('ListViewFolders.tpl', $moduleName);
	}
}
