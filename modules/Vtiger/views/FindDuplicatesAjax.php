<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_FindDuplicatesAjax_View extends Vtiger_FindDuplicates_View {

	function process (Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && method_exists($this, $mode)) {
			$this->$mode($request);
		}
	}
	/**
	 * Function to get listView count
	 * @param Vtiger_Request $request
	 */
	/*function getListViewCount(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		if(empty($cvId)) {
			$cvId = '0';
		}

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');

		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		$listViewModel->set('search_key', $searchKey);
		$listViewModel->set('search_value', $searchValue);
		$listViewModel->set('operator', $request->get('operator'));

		$count = $listViewModel->getListViewCount();

		return $count;
	}



	/**
	 * Function to get the page count for list
	 * @return total number of pages
	 */
	/*function getPageCount(Vtiger_Request $request){
		$listViewCount = $this->getListViewCount($request);
		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		$result = array();
		$result['page'] = $pageCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}*/
}