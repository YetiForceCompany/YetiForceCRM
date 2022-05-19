<?php

/**
 * Vtiger pagination view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Pagination_View extends Vtiger_IndexAjax_View
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPagination');
		$this->exposeMethod('getRelationPagination');
	}

	public function getRelationPagination(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$pageNumber = $request->getInteger('page');
		$moduleName = $request->getModule();

		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('noOfEntries', $request->getInteger('noOfEntries'));
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$parentId = $request->getInteger('record');
		if (!$parentId || !\App\Privilege::isPermitted($moduleName, 'DetailView', $parentId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		if ($request->has('entityState')) {
			$relationListView->set('entityState', $request->getByType('entityState'));
		}
		$totalCount = (int) $relationListView->getRelatedEntriesCount();
		if (!empty($totalCount)) {
			$pagingModel->set('totalCount', (int) $totalCount);
		}
		$viewer->assign('LISTVIEW_COUNT', (int) $totalCount);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		echo $viewer->view('Pagination.tpl', $moduleName, true);
	}

	public function getPagination(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$cvId = $request->getByType('viewname', 2);
		$pageNumber = $request->getInteger('page');
		$moduleName = $request->getModule();
		if (empty($cvId)) {
			$cvId = App\CustomView::getInstance($moduleName)->getViewId();
		}
		if (empty($pageNumber)) {
			$pageNumber = App\CustomView::getCurrentPage($moduleName, $cvId);
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('viewid', $cvId);
		$pagingModel->set('noOfEntries', $request->getInteger('noOfEntries'));
		$totalCount = $request->getInteger('totalCount');
		if (App\Config::performance('LISTVIEW_COMPUTE_PAGE_COUNT') || -1 == $totalCount) {
			$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
			$operator = 's';
			if (!$request->isEmpty('operator', true)) {
				$operator = $request->getByType('operator');
				$listViewModel->set('operator', $operator);
			}
			if (!$request->isEmpty('search_key', true) && !$request->isEmpty('search_value', true)) {
				$searchKey = $request->getByType('search_key', 'Alnum');
				$listViewModel->set('search_key', $searchKey);
				$listViewModel->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $searchKey, $operator));
			}
			if ($request->has('entityState')) {
				$listViewModel->set('entityState', $request->getByType('entityState'));
			}
			$searchParams = App\Condition::validSearchParams($moduleName, $request->getArray('search_params'));
			if (!empty($searchParams) && \is_array($searchParams)) {
				$transformedSearchParams = $listViewModel->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
				$listViewModel->set('search_params', $transformedSearchParams);
			}
			if ($advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : []) {
				$listViewModel->set('advancedConditions', \App\Condition::validAdvancedConditions($advancedConditions));
			}
			$totalCount = $listViewModel->getListViewCount();
		}
		if (!empty($totalCount)) {
			$pagingModel->set('totalCount', $totalCount);
			$pagingModel->calculatePageRange($totalCount);
			$pagingModel->set('nextPageExists', ($totalCount > $pageNumber * $pagingModel->getPageLimit()));
		} else {
			$totalCount = false;
		}
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('TOTAL_ENTRIES', $totalCount);
		$viewer->assign('PAGE_COUNT', $pagingModel->getPageCount());
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $pagingModel->getStartPagingFrom());
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->view('Pagination.tpl', $moduleName);
	}
}
