<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_DetailAjax_Action extends Vtiger_BasicAjax_Action{
    
    public function __construct() {
        parent::__construct();
		$this->exposeMethod('getRecordsCount');
	}
    
    public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    /**
	 * Function to get related Records count from this relation
	 * @param <Vtiger_Request> $request
	 * @return <Number> Number of record from this relation
	 */
	public function getRecordsCount(Vtiger_Request $request) {
		$record = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($record);
		$reportModel->setModule('Reports');
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
        
        $advFilterSql = $reportModel->getAdvancedFilterSQL();
        $query = $reportModel->getReportSQL($advFilterSql, 'PDF');
        $countQuery = $reportModel->generateCountQuery($query);

        $count = $reportModel->getReportsCount($countQuery);
        $response = new Vtiger_Response();
        $response->setResult($count);
        $response->emit();
    }
    
}