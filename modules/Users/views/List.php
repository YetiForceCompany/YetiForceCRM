<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Users_List_View extends Settings_Vtiger_List_View
{
	use \App\Controller\Traits\SettingsPermission;

	/**
	 * List view model instance.
	 *
	 * @var Vtiger_ListView_Model
	 */
	public $listViewModel;

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.List',
			'modules.Users.resources.List',
		]));
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		$viewer->view('ListViewContents.tpl', $request->getModule(false));
	}

	/**
	 * Function to initialize the required data in smarty to display the List View Contents.
	 *
	 * @param \App\Request  $request
	 * @param Vtiger_Viewer $viewer
	 */
	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $request->getModule();
		$cvId = $request->getByType('viewname', 2);
		$pageNumber = $request->getInteger('page');
		$orderBy = $request->getForSql('orderby');
		$sortOrder = $request->getForSql('sortorder');
		$searchResult = $request->get('searchResult');
		if (empty($orderBy) && empty($sortOrder)) {
			$moduleInstance = CRMEntity::getInstance($moduleName);
			$orderBy = $moduleInstance->default_order_by;
			$sortOrder = $moduleInstance->default_sort_order;
		}
		if ('ASC' == $sortOrder) {
			$nextSortOrder = 'DESC';
			$sortImage = 'fas fa-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'fas fa-chevron-up';
		}
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		if (!$this->listViewModel) {
			$this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		}
		$linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->getByType('view', 1), 'CVID' => $cvId];
		$linkModels = $this->listViewModel->getListViewMassActions($linkParams);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('viewid', $cvId);

		if (!empty($orderBy)) {
			$this->listViewModel->set('orderby', $orderBy);
			$this->listViewModel->set('sortorder', $sortOrder);
		}
		$operator = '';
		if (!$request->isEmpty('operator', true)) {
			$operator = $request->getByType('operator');
			$this->listViewModel->set('operator', $operator);
		}
		$viewer->assign('OPERATOR', $operator);
		if (!$request->isEmpty('search_key', true)) {
			$searchKey = $request->getByType('search_key', 'Alnum');
			$searchValue = App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $searchKey, $operator);
			if (!empty($searchKey) && !empty($searchValue)) {
				$this->listViewModel->set('search_key', $searchKey);
				$this->listViewModel->set('search_value', $searchValue);
				if ('status' != $searchKey) {
					$viewer->assign('ALPHABET_VALUE', $searchValue);
				}
			}
		}
		$searchParams = App\Condition::validSearchParams($moduleName, $request->getArray('search_params'));
		if (empty($searchParams) || !\is_array($searchParams) || empty($searchParams[0])) {
			$searchParamsRaw = $searchParams = [[['status', 'e', 'Active']]];
		}
		$this->listViewModel->loadSearchLockedFields($request);
		$transformedSearchParams = $this->listViewModel->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
		$this->listViewModel->set('search_params', $transformedSearchParams);

		//To make smarty to get the details easily accesible
		foreach ($request->getArray('search_params') as $fieldListGroup) {
			$searchParamsRaw[] = $fieldListGroup;
			foreach ($fieldListGroup as $fieldSearchInfo) {
				$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2] ?? '';
				$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0] ?? '';
				$fieldSearchInfo['specialOption'] = $fieldSearchInfo[3] ?? '';
				$searchParams[$fieldName] = $fieldSearchInfo;
			}
		}
		if (!empty($searchResult) && \is_array($searchResult)) {
			$this->listViewModel->getQueryGenerator()->addNativeCondition(['vtiger_crmentity.crmid' => $searchResult]);
		}
		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $this->listViewModel->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			$this->listViewEntries = $this->listViewModel->getListViewEntries($pagingModel);
		}
		$noOfEntries = \count($this->listViewEntries);

		$viewer->assign('MODULE', $moduleName);

		if (!isset($this->listViewLinks)) {
			$this->listViewLinks = $this->listViewModel->getListViewLinks($linkParams);
		}
		$viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);
		$viewer->assign('LISTVIEW_MASSACTIONS', $linkModels['LISTVIEWMASSACTION']);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('COLUMN_NAME', $orderBy);
		$viewer->assign('QUALIFIED_MODULE', $moduleName);
		$viewer->assign('ALPHABET_VALUE', $searchValue ?? '');
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);

		if (App\Config::performance('LISTVIEW_COMPUTE_PAGE_COUNT') && empty($this->listViewCount)) {
			$this->listViewCount = $this->listViewModel->getListViewCount();
		}
		if (!isset($this->listViewCount)) {
			$this->listViewCount = 0;
		}
		$pagingModel->set('totalCount', (int) $this->listViewCount);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();
		$viewer->assign('LISTVIEW_COUNT', (int) $this->listViewCount);
		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('MODULE_MODEL', $this->listViewModel->getModule());
		$viewer->assign('VIEW_MODEL', $this->listViewModel);
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('IS_MODULE_EDITABLE', $this->listViewModel->getModule()->isPermitted('CreateView'));
		$viewer->assign('IS_MODULE_DELETABLE', $this->listViewModel->getModule()->isPermitted('Delete'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SEARCH_DETAILS', $searchParams);
		$viewer->assign('SEARCH_PARAMS', $searchParamsRaw);
		$viewer->assign('ADVANCED_CONDITIONS', []);
		$viewer->assign('LOCKED_EMPTY_FIELDS', $request->isEmpty('lockedEmptyFields', true) ? [] : $request->getArray('lockedEmptyFields'));
	}

	/**
	 * Function returns the number of records for the current filter.
	 *
	 * @param \App\Request $request
	 */
	public function getRecordsCount(App\Request $request)
	{
		$moduleName = $request->getModule();
		$cvId = $request->getByType('viewname', 2);
		$count = $this->getListViewCount($request);

		$result = [];
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get listView count.
	 *
	 * @param \App\Request $request
	 */
	public function getListViewCount(App\Request $request)
	{
		$moduleName = $request->getModule();
		$cvId = App\CustomView::getInstance($moduleName)->getViewId();
		if (empty($cvId)) {
			$cvId = '0';
		}
		$searchKey = $request->getByType('search_key', 2);
		$searchValue = $request->get('search_value');
		$searchParams = App\Condition::validSearchParams($moduleName, $request->getArray('search_params'));
		$operator = $request->getByType('operator');
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);

		if (empty($searchParams) || !\is_array($searchParams)) {
			$searchParams = [];
		}
		$transformedSearchParams = $listViewModel->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
		$listViewModel->set('search_params', $transformedSearchParams);
		if (!empty($operator)) {
			$listViewModel->set('operator', $operator);
		}
		if (!empty($searchKey) && !empty($searchValue)) {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
		}
		return $listViewModel->getListViewCount();
	}

	/**
	 * Function to get the page count for list.
	 *
	 * @param \App\Request $request
	 *
	 * @return total number of pages
	 */
	public function getPageCount(App\Request $request)
	{
		$listViewCount = $this->getListViewCount($request);
		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		if (0 == $pageCount) {
			$pageCount = 1;
		}
		$result = [];
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $listViewCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
