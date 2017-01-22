<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_List_View extends Vtiger_Index_View
{

	protected $listViewEntries = false;
	protected $listViewCount = false;
	protected $listViewLinks = false;
	protected $listViewHeaders = false;
	protected $listViewModel;
	protected $viewName;

	public function __construct()
	{
		parent::__construct();
	}

	public function getPageTitle(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleName = $moduleName == 'Vtiger' ? 'YetiForce' : $moduleName;
		$title = vtranslate($moduleName, $moduleName);
		$title = $title . ' - ' . vtranslate('LBL_VIEW_LIST', $moduleName);

		if ($request->has('viewname')) {
			$customView = CustomView_Record_Model::getAll($moduleName)[$request->get('viewname')];
			if (!empty($customView)) {
				$title .= ' [' . vtranslate('LBL_FILTER', $moduleName) . ': ' . vtranslate($customView->get('viewname'), $moduleName) . ']';
			}
		}
		return $title;
	}

	public function getBreadcrumbTitle(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$title = vtranslate('LBL_VIEW_LIST', $moduleName);
		if ($request->has('viewname')) {
			$customView = CustomView_Record_Model::getAll($moduleName)[$request->get('viewname')];
			if (!empty($customView)) {
				$title .= '<div class="breadCrumbsFilter dispaly-inline font-small"> [' . vtranslate('LBL_FILTER', $moduleName)
					. ': ' . vtranslate($customView->get('viewname'), $moduleName) . ']</div>';
			}
		}
		return $title;
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);

		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$mid = false;
		if ($request->has('mid')) {
			$mid = $request->get('mid');
		}

		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName, $mid));
		$this->viewName = App\CustomView::getInstance($moduleName)->getViewId();
		$this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $this->viewName);
		$viewer->assign('HEADER_LINKS', $this->listViewModel->getHederLinks($linkParams));
		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('VIEWID', $this->viewName);
		$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	public function preProcessTplName(Vtiger_Request $request)
	{
		return 'ListViewPreProcess.tpl';
	}

	//Note : To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/* function preProcessParentTplName(Vtiger_Request $request) {
	  return parent::preProcessTplName($request);
	  } */

	protected function preProcessDisplay(Vtiger_Request $request)
	{
		parent::preProcessDisplay($request);
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if ($request->isAjax()) {
			if (!isset($this->viewName)) {
				$this->viewName = App\CustomView::getInstance($moduleName)->getViewId();
			}
			if (App\CustomView::hasViewChanged($moduleName, $this->viewName)) {
				$customViewModel = CustomView_Record_Model::getInstanceById($this->viewName);
				if ($customViewModel) {
					App\CustomView::setDefaultSortOrderBy($moduleName, ['orderBy' => $customViewModel->getSortOrderBy('orderBy'), 'sortOrder' => $customViewModel->getSortOrderBy('sortOrder')]);
				}
				App\CustomView::setCurrentView($moduleName, $this->viewName);
			} else {
				App\CustomView::setDefaultSortOrderBy($moduleName);
				if ($request->has('page')) {
					App\CustomView::setCurrentPage($moduleName, $this->viewName, $request->get('page'));
				}
			}
			$this->initializeListViewContents($request, $viewer);
			$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
			$viewer->assign('VIEWID', $this->viewName);
		}
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->view('ListViewContents.tpl', $moduleName);
	}

	public function postProcess(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->view('ListViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.List',
			"modules.$moduleName.resources.List",
			'~libraries/jquery/colorpicker/js/colorpicker.js',
			'modules.CustomView.resources.CustomView',
			"modules.$moduleName.resources.CustomView",
			'modules.Vtiger.resources.CkEditor',
			'modules.Vtiger.resources.ListSearch',
			"modules.$moduleName.resources.ListSearch"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Retrieves css styles that need to loaded in the page
	 * @param Vtiger_Request $request - request model
	 * @return <array> - array of Vtiger_CssScript_Model
	 */
	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = array(
			'~libraries/jquery/colorpicker/css/colorpicker.css'
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		return $headerCssInstances;
	}
	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */

	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $request->getModule();
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$searchResult = $request->get('searchResult');
		if (empty($orderBy) && empty($sortOrder)) {
			$orderBy = App\CustomView::getSortby($moduleName);
			$sortOrder = App\CustomView::getSorder($moduleName);
			if (empty($orderBy)) {
				$moduleInstance = CRMEntity::getInstance($moduleName);
				$orderBy = $moduleInstance->default_order_by;
				$sortOrder = $moduleInstance->default_sort_order;
			}
		}
		if ($sortOrder === 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'glyphicon glyphicon-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'glyphicon glyphicon-chevron-up';
		}
		if (empty($pageNumber)) {
			$pageNumber = App\CustomView::getCurrentPage($moduleName, $this->viewName);
		}
		if (!$this->listViewModel) {
			$this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $this->viewName);
		}
		if (!empty($searchResult)) {
			$this->listViewModel->set('searchResult', $searchResult);
		}
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'), 'CVID' => $this->viewName);
		$linkModels = $this->listViewModel->getListViewMassActions($linkParams);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('viewid', $this->viewName);
		if (!empty($orderBy)) {
			$this->listViewModel->set('orderby', $orderBy);
			$this->listViewModel->set('sortorder', $sortOrder);
		}
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
		if (!empty($operator)) {
			$this->listViewModel->set('operator', $operator);
		}
		$viewer->assign('OPERATOR', $operator);
		$viewer->assign('ALPHABET_VALUE', $searchValue);
		if (!empty($searchKey) && !empty($searchValue)) {
			$this->listViewModel->set('search_key', $searchKey);
			$this->listViewModel->set('search_value', $searchValue);
		}
		$searchParams = $request->get('search_params');
		if (!empty($searchParams) && is_array($searchParams)) {
			$transformedSearchParams = $this->listViewModel->get('query_generator')->parseBaseSearchParamsToCondition($searchParams);
			$this->listViewModel->set('search_params', $transformedSearchParams);
			//To make smarty to get the details easily accesible
			foreach ($searchParams as $fieldListGroup) {
				foreach ($fieldListGroup as $fieldSearchInfo) {
					$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
					$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
					$fieldSearchInfo['specialOption'] = $fieldSearchInfo[3];
					$searchParams[$fieldName] = $fieldSearchInfo;
				}
			}
		} else {
			$searchParams = [];
		}
		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $this->listViewModel->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			$this->listViewEntries = $this->listViewModel->getListViewEntries($pagingModel);
		}
		$noOfEntries = count($this->listViewEntries);
		$viewer->assign('MODULE', $moduleName);
		if (!$this->listViewLinks) {
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
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			if (!$this->listViewCount) {
				$this->listViewCount = $this->listViewModel->getListViewCount();
			}
			$pagingModel->set('totalCount', (int) $this->listViewCount);
			$viewer->assign('LISTVIEW_COUNT', $this->listViewCount);
		}
		$viewer->assign('PAGE_COUNT', $pagingModel->getPageCount());
		$viewer->assign('START_PAGIN_FROM', $pagingModel->getStartPagingFrom());
		$viewer->assign('LIST_VIEW_MODEL', $this->listViewModel);
		$viewer->assign('IS_MODULE_EDITABLE', $this->listViewModel->getModule()->isPermitted('EditView'));
		$viewer->assign('IS_MODULE_DELETABLE', $this->listViewModel->getModule()->isPermitted('Delete'));
		$viewer->assign('SEARCH_DETAILS', $searchParams);
	}
}
