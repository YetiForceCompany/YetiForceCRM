<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Mobile_WS_SaveRecord extends Mobile_WS_FetchRecordWithGrouping {
	protected $recordValues = false;
	
	// Avoid retrieve and return the value obtained after Create or Update
	protected function processRetrieve(Mobile_API_Request $request) {
		return $this->recordValues;
	}
	
	function process(Mobile_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();
		
		$module = $request->get('module');
		$recordid = $request->get('record');
		$valuesJSONString =  $request->get('values');
		
		$values = "";
		if(!empty($valuesJSONString) && is_string($valuesJSONString)) {
			$values = Zend_Json::decode($valuesJSONString);
		} else {
			$values = $valuesJSONString; // Either empty or already decoded.
		}
		
		$response = new Mobile_API_Response();
		
		if (empty($values)) {
			$response->setError(1501, "Values cannot be empty!");
			return $response;
		}
		
		try {
			// Retrieve or Initalize
			if (!empty($recordid) && !$this->isTemplateRecordRequest($request)) {
				$this->recordValues = vtws_retrieve($recordid, $current_user);
			} else {
				$this->recordValues = array();
			}

			// Set the modified values
			foreach($values as $name => $value) {
				$this->recordValues[$name] = $value;
			}
			
			// Update or Create
			if (isset($this->recordValues['id'])) {
				$this->recordValues = vtws_update($this->recordValues, $current_user);
			} else {
				
				// Set right target module name for Calendar/Event record
				if ($module == 'Calendar') {
					if (!empty($this->recordValues['eventstatus']) && $this->recordValues['activitytype'] != 'Task') {
						$module = 'Events';
					}
				}
				$this->recordValues = vtws_create($module, $this->recordValues, $current_user);
			}
			
			// Update the record id
			$request->set('record', $this->recordValues['id']);
			
			// Gather response with full details
			$response = parent::process($request);
			
		} catch(Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		return $response;
	}
	
}