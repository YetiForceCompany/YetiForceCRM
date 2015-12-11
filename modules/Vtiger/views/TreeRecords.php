<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_TreeRecords_View extends Vtiger_Index_View
{

	function preProcess(Vtiger_Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeViewModel = Vtiger_TreeView_Model::getInstance($moduleModel);
		$this->pageTitle = vtranslate($treeViewModel->getName(), $moduleName);

		parent::preProcess($request);
		$viewer = $this->getViewer($request);

		$treeList = $treeViewModel->getTreeList();
		$viewer->assign('TREE_LIST', Zend_Json::encode($treeList));

		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName);
		$quickLinkModels = $listViewModel->getSideBarLinks($linkParams);
		$viewer->assign('QUICK_LINKS', $quickLinkModels);
		$viewer->view('TreeRecordsPreProcess.tpl', $moduleName);
	}

	public function postProcess(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));
		$viewer->view('TreeRecordsPostProcess.tpl', $moduleName);

		parent::postProcess($request);
	}

	function process(Vtiger_Request $request)
	{
		$branches = $request->get('branches');
		$filter = $request->get('filter');
		if (empty($branches)) {
			return;
		}

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeViewModel = Vtiger_TreeView_Model::getInstance($moduleModel);

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $filter);
		$listViewModel->set('search_params', $treeViewModel->getSearchParams($branches));

		$listEntries = $listViewModel->getListViewEntries($pagingModel, true);
		if (count($listEntries) === 0) {
			return;
		}
		$listHeaders = $listViewModel->getListViewHeaders();

		$viewer->assign('ENTRIES', $listEntries);
		$viewer->assign('HEADERS', $listHeaders);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('TreeRecords.tpl', $moduleName);
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$parentScriptInstances = parent::getFooterScripts($request);
		$scripts = [
			'~libraries/jquery/jstree/jstree.min.js',
			'~libraries/jquery/datatables/media/js/jquery.dataTables.min.js',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.min.js',
		];
		$viewInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($viewInstances, $parentScriptInstances);
		return $scriptInstances;
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$parentCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/jquery/jstree/themes/proton/style.css',
			'~libraries/jquery/datatables/media/css/jquery.dataTables_themeroller.css',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($modalInstances, $parentCssInstances);
		return $cssInstances;
	}
}
