<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Mobile_WS_ListModuleRecords extends Mobile_WS_Controller {
	
	function isCalendarModule($module) {
		return ($module == 'Events' || $module == 'Calendar');
	}
	
	function getSearchFilterModel($module, $search) {
		return Mobile_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}
	
	function getPagingModel(Mobile_API_Request $request) {
		$page = $request->get('page', 0);
		return Mobile_WS_PagingModel::modelWithPageStart($page);
	}
	
	function process(Mobile_API_Request $request) {
		return $this->processSearchRecordLabel($request);
	}
	
	function processSearchRecordLabel(Mobile_API_Request $request) {
		global $current_user; // Few core API assumes this variable availability
		
		$current_user = $this->getActiveUser();
		$module = $request->get('module');
		$alertid = $request->get('alertid');
		$filterid = $request->get('filterid');
		$search = $request->get('search');
		
		$filterOrAlertInstance = false;
		if(!empty($alertid)) {
			$filterOrAlertInstance = Mobile_WS_AlertModel::modelWithId($alertid);
		}
		else if(!empty($filterid)) {
			$filterOrAlertInstance = Mobile_WS_FilterModel::modelWithId($module, $filterid);
		}
		else if(!empty($search)) {
			$filterOrAlertInstance = $this->getSearchFilterModel($module, $search);
		}
		
		if($filterOrAlertInstance && strcmp($module, $filterOrAlertInstance->moduleName)) {
			$response = new Mobile_API_Response();
			$response->setError(1001, 'Mistached module information.');
			return $response;
		}

		// Initialize with more information
		if($filterOrAlertInstance) {
			$filterOrAlertInstance->setUser($current_user);
		}
		
		// Paging model
		$pagingModel = $this->getPagingModel($request);

		if($this->isCalendarModule($module)) {
			return $this->processSearchRecordLabelForCalendar($request, $pagingModel);
		}
		$records = $this->fetchRecordLabelsForModule($module, $current_user, array(), $filterOrAlertInstance, $pagingModel);

		$modifiedRecords = array();
		foreach($records as $record) {
			if ($record instanceof SqlResultIteratorRow) {
				$record = $record->data;
				// Remove all integer indexed mappings
				for($index = count($record); $index > -1; --$index) {
					if(isset($record[$index])) {
						unset($record[$index]);
					}
				}
			}
			
			$recordid = $record['id'];
			unset($record['id']);
			
			$eventstart = '';
			if($this->isCalendarModule($module)) {
				$eventstart = $record['date_start'];
				unset($record['date_start']);
			}

			$values = array_values($record);
			$label = implode(' ', $values);
			
			$modifiedRecord = array('id' => $recordid, 'label'=>$label); 
			if(!empty($eventstart)) {
				$modifiedRecord['eventstart'] = $eventstart;
			}
			$modifiedRecords[] = $modifiedRecord;
		}
		
		$response = new Mobile_API_Response();
		$response->setResult(array('records'=>$modifiedRecords, 'module'=>$module));
		
		return $response;
	}
	
	function processSearchRecordLabelForCalendar(Mobile_API_Request $request, $pagingModel = false) {
		$current_user = $this->getActiveUser();
		
		// Fetch both Calendar (Todo) and Event information
		$moreMetaFields = array('date_start', 'time_start', 'activitytype', 'location');
		$eventsRecords = $this->fetchRecordLabelsForModule('Events', $current_user, $moreMetaFields, false, $pagingModel);
		$calendarRecords=$this->fetchRecordLabelsForModule('Calendar', $current_user, $moreMetaFields, false, $pagingModel);

		// Merge the Calendar & Events information
		$records = array_merge($eventsRecords, $calendarRecords);
		
		$modifiedRecords = array();
		foreach($records as $record) {
			$modifiedRecord = array();
			$modifiedRecord['id'] = $record['id'];                      unset($record['id']);
			$modifiedRecord['eventstartdate'] = $record['date_start'];  unset($record['date_start']);
			$modifiedRecord['eventstarttime'] = $record['time_start'];  unset($record['time_start']);
			$modifiedRecord['eventtype'] = $record['activitytype'];     unset($record['activitytype']);
			$modifiedRecord['eventlocation'] = $record['location'];     unset($record['location']);
			
			$modifiedRecord['label'] = implode(' ',array_values($record));
			
			$modifiedRecords[] = $modifiedRecord;
		}
		
		$response = new Mobile_API_Response();
		$response->setResult(array('records' =>$modifiedRecords, 'module'=>'Calendar'));
		
		return $response;
	}
	
	function fetchRecordLabelsForModule($module, $user, $morefields=array(), $filterOrAlertInstance=false, $pagingModel = false) {
		if($this->isCalendarModule($module)) {
			$fieldnames = Mobile_WS_Utils::getEntityFieldnames('Calendar');
		} else {
			$fieldnames = Mobile_WS_Utils::getEntityFieldnames($module);
		}
		
		if(!empty($morefields)) {
			foreach($morefields as $fieldname) $fieldnames[] = $fieldname;
		}

		if($filterOrAlertInstance === false) {
			$filterOrAlertInstance = Mobile_WS_SearchFilterModel::modelWithCriterias($module);
			$filterOrAlertInstance->setUser($user);
		}
			
		return $this->queryToSelectFilteredRecords($module, $fieldnames, $filterOrAlertInstance, $pagingModel);
	}
	
	function queryToSelectFilteredRecords($module, $fieldnames, $filterOrAlertInstance, $pagingModel) {
		
		if ($filterOrAlertInstance instanceof Mobile_WS_SearchFilterModel) {
			return $filterOrAlertInstance->execute($fieldnames, $pagingModel);
		}
		
		global $adb;
		
		$moduleWSId = Mobile_WS_Utils::getEntityModuleWSId($module);
		$columnByFieldNames = Mobile_WS_Utils::getModuleColumnTableByFieldNames($module, $fieldnames);

		// Build select clause similar to Webservice query
		$selectColumnClause = "CONCAT('{$moduleWSId}','x',vtiger_crmentity.crmid) as id,";
		foreach($columnByFieldNames as $fieldname=>$fieldinfo) {
			$selectColumnClause .= sprintf("%s.%s as %s,", $fieldinfo['table'],$fieldinfo['column'],$fieldname);
		}
		$selectColumnClause = rtrim($selectColumnClause, ',');
		
		$query = $filterOrAlertInstance->query();
		$query = preg_replace("/SELECT.*FROM(.*)/i", "SELECT $selectColumnClause FROM $1", $query);
		
		if ($pagingModel !== false) {
			$query .= sprintf(" LIMIT %s, %s", $pagingModel->currentCount(), $pagingModel->limit());
		}

		$prequeryResult = $adb->pquery($query, $filterOrAlertInstance->queryParameters());
		return new SqlResultIterator($adb, $prequeryResult);
	}
	
}
