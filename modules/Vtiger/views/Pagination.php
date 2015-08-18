<?php

class Vtiger_Pagination_View extends Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPagination');
	}

	public function getPagination(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$pageNumber = $request->get('page');
		$searchResult = $request->get('searchResult');
		$moduleName = $request->getModule();
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		if (empty($pageNumber)) {
			$pageNumber = '1';
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('viewid', $request->get('viewname'));
		if (!$this->listViewEntries) {
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel, $searchResult);
		}
		if (!$this->listViewCount) {
			$this->listViewCount = $listViewModel->getListViewCount();
		}
		$noOfEntries = count($this->listViewEntries);
		$totalCount = $this->listViewCount;
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $totalCount / (int) $pageLimit);

		if ($pageCount == 0) {
			$pageCount = 1;
		}
		$viewer->assign('PAGE_COUNT', $pageCount);

	
		$startPaginFrom = $pageNumber - 2;

		if ($pageNumber == $totalCount && 1 != $pageNumber)
			$startPaginFrom = $pageNumber - 4;
		if ($startPaginFrom <= 0 || 1 == $pageNumber)
			$startPaginFrom = 1;
		
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		echo $viewer->view('Pagination.tpl', $moduleName, true);
	}
}
