<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once dirname(__FILE__) . '/QueryWithGrouping.php';

class Mobile_WS_RelatedRecordsWithGrouping extends Mobile_WS_QueryWithGrouping {
	
	function process(Mobile_API_Request $request) {
		global $current_user, $adb, $currentModule;
		$current_user = $this->getActiveUser();
		
		$response = new Mobile_API_Response();

		$record = $request->get('record');
		$relatedmodule = $request->get('relatedmodule');
		$currentPage = $request->get('page', 0);
		
		// Input validation
		if (empty($record)) {
			$response->setError(1001, 'Record id is empty');
			return $response;
		}
		$recordid = vtws_getIdComponents($record);
		$recordid = $recordid[1];
		
		$module = Mobile_WS_Utils::detectModulenameFromRecordId($record);

		// Initialize global variable
		$currentModule = $module;
		
		$functionHandler = Mobile_WS_Utils::getRelatedFunctionHandler($module, $relatedmodule); 
		
		if ($functionHandler) {
			$sourceFocus = CRMEntity::getInstance($module);
			$relationResult = call_user_func_array(	array($sourceFocus, $functionHandler), array($recordid, getTabid($module), getTabid($relatedmodule)) );
			$query = $relationResult['query'];
		
			$querySEtype = "vtiger_crmentity.setype as setype";
			if ($relatedmodule == 'Calendar') {
				$querySEtype = "vtiger_activity.activitytype as setype";
			}
			
			$query = sprintf("SELECT vtiger_crmentity.crmid, $querySEtype %s", substr($query, stripos($query, 'FROM')));
			$queryResult = $adb->query($query);
			
			// Gather resolved record id's
			$relatedRecords = array();
			while($row = $adb->fetch_array($queryResult)) {
				$targetSEtype = $row['setype'];
				if ($relatedmodule == 'Calendar') {
					if ($row['setype'] != 'Task' && $row['setype'] != 'Emails') {
						$targetSEtype = 'Events';
					} else {
						$targetSEtype = $relatedmodule;
					}
				}
				$relatedRecords[] = sprintf("%sx%s", Mobile_WS_Utils::getEntityModuleWSId($targetSEtype), $row['crmid']);
			}
			
			// Perform query to get record information with grouping
			$wsquery = sprintf("SELECT * FROM %s WHERE id IN ('%s');", $relatedmodule, implode("','", $relatedRecords));
			$newRequest = new Mobile_API_Request();
			$newRequest->set('module', $relatedmodule);
			$newRequest->set('query', $wsquery);
			$newRequest->set('page', $currentPage);

			$response = parent::process($newRequest);
		}
		
		return $response;
	}
}