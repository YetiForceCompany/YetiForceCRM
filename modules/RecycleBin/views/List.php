<?php

/**
 * RecycleBin list View Class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Class RecycleBin_List_View.
 */
class RecycleBin_List_View extends Vtiger_List_View
{
	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = RecycleBin_Module_Model::getInstance($moduleName);
		$viewer->assign('HEADER_LINKS', ['LIST_VIEW_HEADER' => []]);
		$viewer->assign('MODULE_LIST', $moduleModel->getAllModuleList());
		$this->preProcessDisplay($request);
	}

	/** {@inheritdoc} */
	public function preProcessTplName(App\Request $request)
	{
		return 'ListViewPreProcess.tpl';
	}

	/** {@inheritdoc} */
	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 2);
		$pageNumber = $request->isEmpty('page', true) ? 1 : $request->getInteger('page');
		$orderBy = $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL);
		$moduleModel = RecycleBin_Module_Model::getInstance($moduleName);
		if (empty($sourceModule)) {
			$sourceModule = current($moduleModel->getAllModuleList())['name'];
		}
		if (empty($orderBy)) {
			$orderBy = \App\CustomView::getSortBy($sourceModule);
		}
		$listViewModel = RecycleBin_ListView_Model::getInstance($moduleName, $sourceModule);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
		}
		$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		$linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->getByType('view', 1)];
		$linkModels = $listViewModel->getListViewMassActions($linkParams);
		if (!$this->listViewLinks) {
			$this->listViewLinks = $listViewModel->getListViewLinks($linkParams);
		}
		$totalCount = false;
		if (App\Config::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			if (!$this->listViewCount) {
				$this->listViewCount = $this->listViewModel->getListViewCount();
			}
			$pagingModel->set('totalCount', (int) $this->listViewCount);
			$totalCount = (int) $this->listViewCount;
		}
		$noOfEntries = \count($this->listViewEntries);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEW_MODEL', $this->listViewModel);
		$viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);
		$viewer->assign('LISTVIEW_MASSACTIONS', $linkModels['LISTVIEWMASSACTION'] ?? []);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('PAGE_COUNT', $pagingModel->getPageCount());
		$viewer->assign('START_PAGIN_FROM', $pagingModel->getStartPagingFrom());
		$viewer->assign('COLUMN_NAME', $orderBy);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('IS_MODULE_EDITABLE', false);
		$viewer->assign('IS_MODULE_DELETABLE', false);
		$viewer->assign('SEARCH_PARAMS', []);
		$viewer->assign('ADVANCED_CONDITIONS', []);
		$viewer->assign('LOCKED_EMPTY_FIELDS', []);
	}

	/** {@inheritdoc} */
	public function postProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->view('ListViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.{$request->getModule()}.resources.List",
		]));
	}
}
