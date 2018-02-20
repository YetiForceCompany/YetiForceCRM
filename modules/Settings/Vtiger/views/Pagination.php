<?php

/**
 * Settings OSSMailView index view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Vtiger_Pagination_View extends Settings_Vtiger_IndexAjax_View
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPagination');
	}

	/**
	 * Pagination.
	 *
	 * @param \App\Request $request
	 */
	public function getPagination(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$pageNumber = $request->getInteger('page');
		$searchResult = $request->get('searchResult');
		$qualifiedModuleName = $request->getModule(false);
		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		if (!$request->isEmpty('sourceModule')) {
			$sourceModule = $request->getByType('sourceModule', 2);
			$listViewModel->set('sourceModule', $sourceModule);
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('viewid', $request->getByType('viewname', 2));
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->getByType('operator', 1);
		if (!empty($operator)) {
			$listViewModel->set('operator', $operator);
			$viewer->assign('OPERATOR', $operator);
			$viewer->assign('ALPHABET_VALUE', $searchValue);
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
		if (!empty($searchResult) && is_array($searchResult)) {
			$listViewModel->get('query_generator')->addNativeCondition(['vtiger_crmentity.crmid' => $searchResult]);
		}
		if (!property_exists($this, 'listViewEntries') || empty($this->listViewEntries)) {
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		if (!property_exists($this, 'listViewCount') || empty($this->listViewCount)) {
			$this->listViewCount = $listViewModel->getListViewCount();
		}
		$noOfEntries = count($this->listViewEntries);
		$totalCount = $this->listViewCount;
		$pagingModel->set('totalCount', (int) $totalCount);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		echo $viewer->view('Pagination.tpl', $moduleName, true);
	}

	public function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel)
	{
		return Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel);
	}
}
