<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Portal_List_View extends Vtiger_Index_View {

    function preProcess(Vtiger_Request $request, $display=true) {
        parent::preProcess($request);

        $viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
        $viewer->view('ListViewHeader.tpl', $request->getModule(false));
    }

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);

        $this->initializeListViewContents($request, $viewer);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->view('ListViewContents.tpl', $moduleName);
    }

    public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
        $moduleName = $request->getModule();
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$searchValue = $request->get('search_value');

        /*if(empty($orderBy) && empty($searchValue) && empty($pageNumber)) {
            $orderParams = Vtiger_ListView_Model::getSortParamsSession($moduleName);
            if($orderParams) {
                $pageNumber = $orderParams['page'];
                $orderBy = $orderParams['orderby'];
                $sortOrder = $orderParams['sortorder'];
                $searchValue = $orderParams['search_value'];
            }
        } else {
            $params = array('page' => $pageNumber, 'orderby' => $orderBy, 'sortorder' => $sortOrder, 'search_value' => $searchValue);
            Vtiger_ListView_Model::setSortParamsSession($moduleName, $params);
        }*/
		
		if($sortOrder == "ASC"){
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		}else{
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}

		if(empty ($pageNumber)){
			$pageNumber = '1';
		}

        $pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);

        $listViewModel = new Portal_ListView_Model();

        if(!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder',$sortOrder);
		}
        if(!empty($searchValue)) {
			$listViewModel->set('search_value', $searchValue);
		}

        $listviewEntries = $listViewModel->getListViewEntries($pagingModel);

        $viewer->assign('LISTVIEW_ENTRIES', $listviewEntries);
        $viewer->assign('ALPHABET_VALUE', $searchValue);
        $viewer->assign('COLUMN_NAME', $orderBy);
        $viewer->assign('SORT_ORDER', $sortOrder);
        $viewer->assign('SORT_IMAGE', $sortImage);
        $viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
        $viewer->assign('RECORD_COUNT', count($listviewEntries));
        $viewer->assign('CURRENT_PAGE', $pageNumber);
        $viewer->assign('PAGING_INFO', $listViewModel->calculatePageRange($listviewEntries, $pagingModel));
    }

    function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.List',
			"modules.$moduleName.resources.List",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
