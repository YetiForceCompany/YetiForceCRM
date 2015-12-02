<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

Class Calendar_Edit_View extends Vtiger_Edit_View
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('Events');
		$this->exposeMethod('Calendar');
	}

	function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();

		$recordId = $request->get('record');
		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$mode = $recordModel->getType();
		}

		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request, $mode);
			return;
		}
		$this->Calendar($request, 'Calendar');
	}

	function Events($request, $moduleName)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$viewer = $this->getViewer($request);
		$record = $request->get('record');

		if (!empty($record) && $request->get('isDuplicate') == true) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('MODE', '');
		} else if (!empty($record)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('MODE', 'edit');
			$viewer->assign('RECORD_ID', $record);
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$viewer->assign('MODE', '');
		}
		$eventModule = Vtiger_Module_Model::getInstance($moduleName);
		$recordModel->setModuleFromInstance($eventModule);

		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

		foreach ($requestFieldList as $fieldName => $fieldValue) {
			$fieldModel = $fieldList[$fieldName];
			$specialField = false;
			// We collate date and time part together in the EditView UI handling 
			// so a bit of special treatment is required if we come from QuickCreate 
			if (empty($record) && ($fieldName == 'time_start' || $fieldName == 'time_end') && !empty($fieldValue)) {
				$specialField = true;
				// Convert the incoming user-picked time to GMT time 
				// which will get re-translated based on user-time zone on EditForm 
				$fieldValue = DateTimeField::convertToDBTimeZone($fieldValue)->format("H:i");
			}
			if (empty($record) && ($fieldName == 'date_start' || $fieldName == 'due_date') && !empty($fieldValue)) {
				if ($fieldName == 'date_start') {
					$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($requestFieldList['time_start']);
					$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($fieldValue . " " . $startTime);
					list($startDate, $startTime) = explode(' ', $startDateTime);
					$fieldValue = Vtiger_Date_UIType::getDisplayDateValue($startDate);
				} else {
					$endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($requestFieldList['time_end']);
					$endDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($fieldValue . " " . $endTime);
					list($endDate, $endTime) = explode(' ', $endDateTime);
					$fieldValue = Vtiger_Date_UIType::getDisplayDateValue($endDate);
				}
			}

			if ($fieldModel->isEditable() || $specialField) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);

		$viewMode = $request->get('view_mode');
		if (!empty($viewMode)) {
			$viewer->assign('VIEW_MODE', $viewMode);
		}

		$userChangedEndDateTime = $request->get('userChangedEndDateTime');
		//If followup value is passed from request to process the value and sent to client
		$requestFollowUpDate = $request->get('followup_date_start');
		$requestFollowUpTime = $request->get('followup_time_start');
		$followUpStatus = $request->get('followup');

		if (!empty($requestFollowUpDate)) {
			$followUpDate = $requestFollowUpDate;
		}
		if (!empty($requestFollowUpTime)) {
			$followUpTime = $requestFollowUpTime;
		}
		if ($followUpStatus == 'on') {
			$viewer->assign('FOLLOW_UP_STATUS', TRUE);
		}

		$viewer->assign('USER_CHANGED_END_DATE_TIME', $userChangedEndDateTime);
		$viewer->assign('FOLLOW_UP_DATE', $followUpDate);
		$viewer->assign('FOLLOW_UP_TIME', $followUpTime);
		$viewer->assign('RECURRING_INFORMATION', $recordModel->getRecurrenceInformation());
		$viewer->assign('TOMORROWDATE', Vtiger_Date_UIType::getDisplayDateValue(date('Y-m-d', time() + 86400)));

		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$isRelationOperation = $request->get('relationOperation');

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if ($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
			$viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
		}
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
		$accessibleUsers = $currentUser->getAccessibleUsers();

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Zend_Json::encode($picklistDependencyDatasource));
		$mappingRelatedField = $moduleModel->getMappingRelatedField($moduleName);
		$viewer->assign('MAPPING_RELATED_FIELD', Zend_Json::encode($mappingRelatedField));
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('INVITIES_SELECTED', $recordModel->getInvities());
		$viewer->assign('CURRENT_USER', $currentUser);

		$viewer->view('EditView.tpl', $moduleName);
	}

	function Calendar($request, $moduleName)
	{
		parent::process($request);
	}
}
