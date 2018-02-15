<?php

/**
 * Reports Pagination view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Reports_Pagination_View extends Vtiger_IndexAjax_View
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPagination');
	}

	public function getPagination(\App\Request $request)
	{
		parent::preProcess($request, false);

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$folders = $moduleModel->getFolders();
		$listViewModel = new Reports_ListView_Model();
		$listViewModel->set('module', $moduleModel);

		$folderId = $request->getByType('viewname', 2);
		if (empty($folderId) || $folderId == 'undefined') {
			$folderId = 'All';
		}
		$sortBy = $request->getForSql('sortorder');
		$orderBy = $request->getForSql('orderby');

		$listViewModel->set('folderid', $folderId);
		$listViewModel->set('orderby', $orderBy);
		$listViewModel->set('sortorder', $sortBy);

		$linkModels = $listViewModel->getListViewLinks(false);
		$pageNumber = $request->getInteger('page');
		$listViewMassActionModels = $listViewModel->getListViewMassActions(false);

		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$viewer->assign('PAGING_MODEL', $pagingModel);

		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}

		$noOfEntries = count($this->listViewEntries);

		$viewer->assign('LISTVIEW_LINKS', $linkModels);
		$viewer->assign('FOLDERS', $folders);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEWNAME', $folderId);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('LISTVIEW_MASSACTIONS', $listViewMassActionModels);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);

		if (!$this->listViewCount) {
			$this->listViewCount = $listViewModel->getListViewCount();
		}
		$totalCount = $this->listViewCount;
		$pagingModel->set('totalCount', (int) $totalCount);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		echo $viewer->view('Pagination.tpl', $moduleName, true);
	}
}
