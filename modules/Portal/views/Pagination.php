<?php

/**
 * Portal pagination view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Portal_Pagination_View extends Vtiger_IndexAjax_View
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
		$pageNumber = $request->getInteger('page');
		$orderBy = $request->getForSql('orderby');
		$sortOrder = $request->getForSql('sortorder');
		$searchValue = $request->getForSql('search_value');

		if ($sortOrder == 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'fas fa-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'fas fa-chevron-up';
		}

		if (empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);

		$listViewModel = new Portal_ListView_Model();

		if (!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}
		if (!empty($searchValue)) {
			$listViewModel->set('search_value', $searchValue);
		}

		$listviewEntries = $listViewModel->getListViewEntries($pagingModel);
		$pagingInfo = $listViewModel->calculatePageRange($listviewEntries, $pagingModel);
		$pagingModel->set('totalCount', $pagingInfo['recordCount']);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('LISTVIEW_COUNT', $pagingInfo['recordCount']);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('LISTVIEW_ENTRIES', $listviewEntries);
		$viewer->assign('ALPHABET_VALUE', $searchValue);
		$viewer->assign('COLUMN_NAME', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('RECORD_COUNT', count($listviewEntries));
		$viewer->assign('CURRENT_PAGE', $pageNumber);
		$viewer->assign('PAGING_INFO', $pagingInfo);
		echo $viewer->view('Pagination.tpl', $moduleName, true);
	}
}
