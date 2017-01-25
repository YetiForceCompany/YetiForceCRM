<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class RecycleBin_List_View extends Vtiger_Index_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$moduleModel = RecycleBin_Module_Model::getInstance($moduleName);

		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));

		$quickLinkModels = $moduleModel->getSideBarLinks($linkParams);

		$viewer->assign('QUICK_LINKS', $quickLinkModels);
		$this->initializeListViewContents($request, $viewer);

		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	public function preProcessTplName(Vtiger_Request $request)
	{
		return 'ListViewPreProcess.tpl';
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		$viewer->view('ListViewContents.tpl', $request->getModule());
	}

	public function postProcess(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('ListViewPostProcess.tpl', $request->getModule());
		parent::postProcess($request);
	}
	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */

	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->get('sourceModule');

		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if ($sortOrder == "ASC") {
			$nextSortOrder = "DESC";
			$sortImage = "glyphicon glyphicon-chevron-down";
		} else {
			$nextSortOrder = "ASC";
			$sortImage = "glyphicon glyphicon-chevron-up";
		}

		if (empty($pageNumber)) {
			$pageNumber = '1';
		}

		$moduleModel = RecycleBin_Module_Model::getInstance($moduleName);
		//If sourceModule is empty, pick the first module name from the list
		if (empty($sourceModule)) {
			foreach ($moduleModel->getAllModuleList() as $model) {
				$sourceModule = $model->get('name');
				break;
			}
		}
		$listViewModel = RecycleBin_ListView_Model::getInstance($moduleName, $sourceModule);

		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
		$linkModels = $moduleModel->getListViewMassActions($linkParams);

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (empty($orderBy) && empty($sortOrder)) {
			$moduleInstance = CRMEntity::getInstance($moduleName);
			$orderBy = $moduleInstance->default_order_by;
			$sortOrder = $moduleInstance->default_sort_order;
		}
		if (!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}

		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		$noOfEntries = count($this->listViewEntries);

		$viewer->assign('MODULE', $moduleName);

		$viewer->assign('LISTVIEW_LINKS', $moduleModel->getListViewLinks(false));
		$viewer->assign('LISTVIEW_MASSACTIONS', $linkModels);

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
		$viewer->assign('MODULE_LIST', $moduleModel->getAllModuleList());
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('DELETED_RECORDS_TOTAL_COUNT', $moduleModel->getDeletedRecordsTotalCount());

		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			if (!$this->listViewCount) {
				$this->listViewCount = $listViewModel->getListViewCount();
			}
			$pagingModel->set('totalCount', (int) $this->listViewCount);
			$viewer->assign('LISTVIEW_COUNT', $this->listViewCount);
		}
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('IS_MODULE_DELETABLE', $listViewModel->getModule()->isPermitted('Delete'));
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
			'modules.CustomView.resources.CustomView',
			"modules.$moduleName.resources.CustomView",
			'modules.Vtiger.resources.CkEditor'
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Function to get the page count for list
	 * @return total number of pages
	 */
	public function getPageCount(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->get('sourceModule');
		$listViewModel = RecycleBin_ListView_Model::getInstance($moduleName, $sourceModule);

		$listViewCount = $listViewModel->getListViewCount($request);
		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		if ($pageCount == 0) {
			$pageCount = 1;
		}
		$result = array();
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $listViewCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function returns the number of records for the current filter
	 * @param Vtiger_Request $request
	 */
	public function getRecordsCount(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->get('sourceModule');
		$listViewModel = RecycleBin_ListView_Model::getInstance($moduleName, $sourceModule);

		$count = $listViewModel->getListViewCount();

		$result = array();
		$result['module'] = $moduleName;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}
