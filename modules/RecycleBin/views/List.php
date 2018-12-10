<?php

/**
 * RecycleBin list View Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class RecycleBin_List_View extends Vtiger_List_View
{
	/**
	 * {@inheritdoc}
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = RecycleBin_Module_Model::getInstance($moduleName);
		$viewer->assign('HEADER_LINKS', ['LIST_VIEW_HEADER' => []]);
		$viewer->assign('MODULE_LIST', $moduleModel->getAllModuleList());
		$this->preProcessDisplay($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessTplName(\App\Request $request)
	{
		return 'ListViewPreProcess.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function initializeListViewContents(\App\Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->get('sourceModule');
		$pageNumber = $request->isEmpty('page', true) ? 1 : $request->getInteger('page');
		$moduleModel = RecycleBin_Module_Model::getInstance($moduleName);
		if (empty($sourceModule)) {
			foreach ($moduleModel->getAllModuleList() as $model) {
				$sourceModule = $model->get('name');
				break;
			}
		}
		$listViewModel = RecycleBin_ListView_Model::getInstance($moduleName, $sourceModule);
		$listViewModel->set('entityState', 'Trash');
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		$orderBy = $request->getForSql('orderby');
		$sortOrder = $request->getForSql('sortorder');
		if (empty($orderBy) && empty($sortOrder)) {
			$relatedInstance = CRMEntity::getInstance($sourceModule);
			$orderBy = $relatedInstance->default_order_by;
			$sortOrder = $relatedInstance->default_sort_order;
		}
		$linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->get('view')];
		$linkModels = $listViewModel->getListViewMassActions($linkParams);
		if (!$this->listViewLinks) {
			$this->listViewLinks = $listViewModel->getListViewLinks($linkParams);
			$this->listViewLinks['LISTVIEWBASIC'] = [];
			$this->listViewLinks['LISTVIEWMASSACTION'] = [];
			$this->listViewLinks['LISTVIEW'] = [];
		}
		$noOfEntries = count($this->listViewEntries);
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
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('IS_MODULE_EDITABLE', false);
		$viewer->assign('IS_MODULE_DELETABLE', false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->view('ListViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[] - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			"modules.$moduleName.resources.List"
		];
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}
}
