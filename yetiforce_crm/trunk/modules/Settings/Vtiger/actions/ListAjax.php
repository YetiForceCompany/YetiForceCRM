<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Vtiger_ListAjax_Action extends Settings_Vtiger_ListAjax_View{
    
    public function __construct() {
        parent::__construct();
        $this->exposeMethod('getPageCount');
    }
	/**
	 * Function returns the number of records for the current filter
	 * @param Vtiger_Request $request
	 */
	function getRecordsCount(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$count = $this->getListViewCount($request);

		$result = array();
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
    
    public function getListViewCount(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$sourceModule = $request->get('sourceModule');

		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);
		
		if(!empty($sourceModule)) {
			$listViewModel->set('sourceModule', $sourceModule);
		}

		return $listViewModel->getListViewCount();
    }
    
    public function getPageCount(Vtiger_Request $request) {
        $numOfRecords = $this->getListViewCount($request);
        $pagingModel = new Vtiger_Paging_Model();
        $pageCount = ceil((int) $numOfRecords/(int)($pagingModel->getPageLimit()));
        
		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $numOfRecords;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
    }
}