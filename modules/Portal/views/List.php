<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Portal_List_View extends Vtiger_Index_View
{
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);

		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		$viewer->view('ListViewHeader.tpl', $request->getModule(false));
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		$viewer->view('ListViewContents.tpl', $moduleName);
	}

	public function initializeListViewContents(\App\Request $request, Vtiger_Viewer $viewer)
	{
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
		$viewer->assign('MODULE', $moduleName);
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
	}

	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.List',
			'modules.' . $request->getModule() . '.resources.List',
		]));
	}
}
