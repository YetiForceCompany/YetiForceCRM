<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Vtiger_Pagination_View extends Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPagination');
		$this->exposeMethod('getRelationPagination');
	}

	public function getRelationPagination(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$pageNumber = $request->get('page');
		$moduleName = $request->getModule();

		if (empty($pageNumber)) {
			$pageNumber = '1';
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('noOfEntries', $request->get('noOfEntries'));
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
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

	public function getPagination(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$pageNumber = $request->get('page');
		$searchResult = $request->get('searchResult');
		$moduleName = $request->getModule();

		if (empty($pageNumber)) {
			$pageNumber = '1';
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('viewid', $request->get('viewname'));
		$pagingModel->set('noOfEntries', $request->get('noOfEntries'));
		$totalCount = (int) $request->get('totalCount');
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT') || $totalCount == -1) {
			$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $request->get('viewname'));
			$operator = $request->get('operator');
			if (!empty($operator)) {
				$listViewModel->set('operator', $operator);
			}
			if (!empty($searchKey) && !empty($searchValue)) {
				$listViewModel->set('search_key', $searchKey);
				$listViewModel->set('search_value', $searchValue);
			}
			$searchParmams = $request->get('search_params');
			if (empty($searchParmams) || !is_array($searchParmams)) {
				$searchParmams = [];
			}
			$transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParmams, $listViewModel->getModule());
			$listViewModel->set('search_params', $transformedSearchParams);
			$totalCount = $listViewModel->getListViewCount();
		}
		if (!empty($totalCount)) {
			$pagingModel->set('totalCount', $totalCount);
			$viewer->assign('LISTVIEW_COUNT', $totalCount);
			$viewer->assign('TOTAL_ENTRIES', $totalCount);
		}

		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('OPERATOR', $operator);
		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		echo $viewer->view('Pagination.tpl', $moduleName, true);
	}

	public function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel)
	{
		return Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel);
	}
}
