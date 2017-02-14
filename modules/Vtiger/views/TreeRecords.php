<?php

/**
 * Basic TreeView View Class
 * @package YetiForce.TreeView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TreeRecords_View extends Vtiger_Index_View
{

	public function getBreadcrumbTitle(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeViewModel = Vtiger_TreeView_Model::getInstance($moduleModel);
		$pageTitle = vtranslate($treeViewModel->getName(), $moduleName);
		return $pageTitle;
	}

	public function preProcess(Vtiger_Request $request, $display = true)
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

	public function postProcess(Vtiger_Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));
		if ($display) {
			$this->postProcessDisplay($request);
		}
		parent::postProcess($request);
	}

	protected function postProcessDisplay(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('TreeRecordsPostProcess.tpl', $request->getModule());
	}

	public function process(Vtiger_Request $request)
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

	public function getFooterScripts(Vtiger_Request $request)
	{
		$parentScriptInstances = parent::getFooterScripts($request);

		$scripts = [
			'~libraries/jquery/jstree/jstree.js',
			'~libraries/jquery/jstree/jstree.category.js',
			'~libraries/jquery/jstree/jstree.checkbox.js',
			'~libraries/jquery/datatables/media/js/jquery.dataTables.js',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.js',
		];
		$viewInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($parentScriptInstances, $viewInstances);
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
		$cssInstances = array_merge($parentCssInstances, $modalInstances);
		return $cssInstances;
	}
}
