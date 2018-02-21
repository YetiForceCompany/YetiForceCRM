<?php

/**
 * Basic TreeView View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TreeRecords_View extends Vtiger_Index_View
{
	public function getBreadcrumbTitle(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeViewModel = Vtiger_TreeView_Model::getInstance($moduleModel);
		$pageTitle = \App\Language::translate($treeViewModel->getName(), $moduleName);

		return $pageTitle;
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeViewModel = Vtiger_TreeView_Model::getInstance($moduleModel);

		$treeList = $treeViewModel->getTreeList();
		$viewer = $this->getViewer($request);
		$viewer->assign('TREE_LIST', \App\Json::encode($treeList));
		$viewer->assign('SELECTABLE_CATEGORY', 0);
		$viewer->view('TreeRecordsPreProcess.tpl', $moduleName);
	}

	public function postProcess(\App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));
		if ($display) {
			$this->postProcessDisplay($request);
		}
		parent::postProcess($request);
	}

	protected function postProcessDisplay(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('TreeRecordsPostProcess.tpl', $request->getModule());
	}

	public function process(\App\Request $request)
	{
		$branches = $request->get('branches');
		$filter = $request->getByType('filter', 2);
		if (empty($branches)) {
			return;
		}

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeViewModel = Vtiger_TreeView_Model::getInstance($moduleModel);

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 0);
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $filter);
		$listViewModel->set('search_params', $treeViewModel->getSearchParams($branches));

		$listEntries = $listViewModel->getListViewEntries($pagingModel);
		if (count($listEntries) === 0) {
			return;
		}
		$listHeaders = $listViewModel->getListViewHeaders();

		$viewer->assign('ENTRIES', $listEntries);
		$viewer->assign('HEADERS', $listHeaders);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('TreeRecords.tpl', $moduleName);
	}

	public function getFooterScripts(\App\Request $request)
	{
		$parentScriptInstances = parent::getFooterScripts($request);

		$scripts = [
			'~libraries/jstree/dist/jstree.js',
			'~layouts/resources/libraries/jstree.category.js',
			'~layouts/resources/libraries/jstree.checkbox.js',
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
		];
		$viewInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($parentScriptInstances, $viewInstances);

		return $scriptInstances;
	}

	public function getHeaderCss(\App\Request $request)
	{
		$parentCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
			'~libraries/datatables.net-bs4/css/dataTables.bootstrap4.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($parentCssInstances, $modalInstances);

		return $cssInstances;
	}
}
