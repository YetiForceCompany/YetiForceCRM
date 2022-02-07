<?php

/**
 * Basic TreeView View Class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TreeRecords_View extends Vtiger_Index_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!Vtiger_TreeView_Model::getInstance($request->getModule())->isActive()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function getBreadcrumbTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		$treeViewModel = Vtiger_TreeView_Model::getInstance($moduleName);
		return \App\Language::translate($treeViewModel->getName(), $moduleName);
	}

	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
		$moduleName = $request->getModule();
		$treeViewModel = Vtiger_TreeView_Model::getInstance($moduleName);

		$treeList = $treeViewModel->getTreeList();
		$viewer = $this->getViewer($request);
		$viewer->assign('TREE_LIST', \App\Json::encode($treeList));
		$viewer->view('TreeRecordsPreProcess.tpl', $moduleName);
	}

	public function postProcess(App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));
		if ($display) {
			$this->postProcessDisplay($request);
		}
		parent::postProcess($request);
	}

	protected function postProcessDisplay(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('TreeRecordsPostProcess.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$filter = $request->has('filter') ? $request->getByType('filter', 'Alnum') : \App\CustomView::getInstance($moduleName)->getViewId();
		$viewer->assign('VIEWID', $filter);

		if ($request->isEmpty('branches', true)) {
			return;
		}
		$branches = $request->getArray('branches', 'Text');
		$treeViewModel = Vtiger_TreeView_Model::getInstance($moduleName);
		$field = $treeViewModel->getTreeField();
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $filter);
		$listViewModel->getQueryGenerator()->addCondition($field['fieldname'], implode('##', $branches), 'e');
		$listEntries = $listViewModel->getAllEntries();
		if (0 === \count($listEntries)) {
			return;
		}
		$listHeaders = $listViewModel->getListViewHeaders();

		$viewer->assign('ENTRIES', $listEntries);
		$viewer->assign('HEADERS', $listHeaders);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('TreeRecords.tpl', $moduleName);
	}

	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/jstree/dist/jstree.js',
			'~layouts/resources/libraries/jstree.category.js',
			'~layouts/resources/libraries/jstree.checkbox.js',
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
			'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
			'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js'
		]));
	}

	public function getHeaderCss(App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
			'~libraries/datatables.net-bs4/css/dataTables.bootstrap4.css',
			'~libraries/datatables.net-responsive-bs4/css/responsive.bootstrap4.css'
		]));
	}
}
