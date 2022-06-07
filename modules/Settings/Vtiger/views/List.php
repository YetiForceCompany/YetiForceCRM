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

class Settings_Vtiger_List_View extends Settings_Vtiger_Index_View
{
	protected $listViewEntries = false;
	protected $listViewHeaders = false;

	/**
	 * List view model instance.
	 *
	 * @var Settings_Vtiger_ListView_Model
	 */
	public $listViewModel;

	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		if (!$request->isEmpty('sourceModule')) {
			$sourceModule = $request->getByType('sourceModule', 2);
			$viewer->assign('SOURCE_MODULE', $sourceModule);
		}
		$viewer->view('ListViewHeader.tpl', $request->getModule(false));
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
		$qualifiedModuleName = $request->getModule(false);
		$pageNumber = $request->getInteger('page');
		$orderBy = $request->getForSql('orderby');
		$sortOrder = $request->getForSql('sortorder');
		$searchParams = $request->getArray('searchParams', 'Text');
		$searchKey = $request->isEmpty('search_key') ? false : $request->getByType('search_key', 'Alnum');
		$searchValue = $request->getByType('search_value', 'Text');

		if ('ASC' === $sortOrder) {
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
			$this->listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);
		}
		$model = $this->listViewModel;

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);

		if (!empty($searchKey) && !empty($searchValue)) {
			$model->set('search_key', $searchKey);
			$model->set('search_value', $searchValue);
		}
		if (!empty($searchParams)) {
			$model->set('searchParams', $searchParams);
			$viewer->assign('SEARCH_PARAMS', $searchParams);
		}

		if (!empty($orderBy)) {
			$model->set('orderby', $orderBy);
			$model->set('sortorder', $sortOrder);
		}
		if (!$request->isEmpty('sourceModule')) {
			$sourceModule = $request->getByType('sourceModule', 2);
			$model->set('sourceModule', $sourceModule);
		}
		if (!$request->isEmpty('forModule')) {
			$sourceModule = $request->getByType('forModule', 1);
			$model->set('forModule', $sourceModule);
		}
		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $model->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			$this->listViewEntries = $model->getListViewEntries($pagingModel);
		}
		$noOfEntries = \count($this->listViewEntries);
		if (!isset($this->listViewLinks)) {
			$this->listViewLinks = $model->getListViewLinks();
		}
		$viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);
		$viewer->assign('MODULE_MODEL', $model->getModule());

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
		if (!isset($this->listViewCount)) {
			$this->listViewCount = $model->getListViewCount();
		}
		$totalCount = $this->listViewCount;
		$pagingModel->set('totalCount', (int) $totalCount);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.List',
			'modules.Settings.Vtiger.resources.List',
			"modules.Settings.$moduleName.resources.List",
			"modules.Settings.Vtiger.resources.$moduleName",
			'modules.Vtiger.resources.ListSearch',
			"modules.$moduleName.resources.ListSearch",
		]));
	}
}
