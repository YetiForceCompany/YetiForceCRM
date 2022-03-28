<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Import_List_View extends \App\Controller\View\Page
{
	use \App\Controller\ExposeMethod;

	protected $listViewEntries = false;
	protected $listViewHeaders = false;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getImportDetails');
	}

	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getByType('forModule'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->initializeListViewContents($request, $viewer);
			$moduleName = $request->getByType('forModule');
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->view('ImportPreview.tpl', 'Import');
		}
	}

	// Function to initialize the required data in smarty to display the List View Contents

	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $request->getByType('forModule');
		$cvId = $request->getByType('viewname', 2);
		$pageNumber = $request->getInteger('page');
		$orderBy = $request->getForSql('orderby');
		$sortOrder = $request->getForSql('sortorder');
		if (empty($orderBy) && empty($sortOrder)) {
			$moduleInstance = CRMEntity::getInstance($moduleName);
			$orderBy = $moduleInstance->default_order_by;
			$sortOrder = $moduleInstance->default_sort_order;
		}
		if ('ASC' == $sortOrder) {
			$nextSortOrder = 'DESC';
			$sortImage = 'downArrowSmall.png';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'upArrowSmall.png';
		}

		if (empty($pageNumber)) {
			$pageNumber = 1;
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$listViewModel = Import_ListView_Model::getInstance($moduleName, $cvId);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);

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
		$noOfEntries = \count($this->listViewEntries);
		$viewer->assign('MODULE', $moduleName);

		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER', $pageNumber);

		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());

		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('COLUMN_NAME', $orderBy);

		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
	}

	public function getImportDetails(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$forModule = $request->getByType('forModule');
		$importRecords = Import_Data_Action::getImportDetails(\App\User::getCurrentUserModel(), $forModule);
		$viewer->assign('IMPORT_RECORDS', $importRecords);
		$viewer->assign('TYPE', $request->get('type'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('FOR_MODULE', $forModule);
		$viewer->view('ImportDetails.tpl', 'Import');
	}
}
