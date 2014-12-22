<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_Save_Action extends Vtiger_Save_Action {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if(!Users_Privileges_Model::isPermitted($moduleName, 'Save', $record)) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request) {
		$recordModel = $this->saveRecord($request);
		$loadUrl = $recordModel->getDetailViewUrl();

		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentRecordId = $request->get('sourceRecord');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
			//TODO : Url should load the related list instead of detail view of record
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->get('returnToList')) {
			$moduleModel = $recordModel->getModule();
			$listViewUrl = $moduleModel->getListViewUrl();

			if ($recordModel->get('visibility') === 'Private') {
				$loadUrl = $listViewUrl;
			} else {
				$userId = $recordModel->get('assigned_user_id');
				$sharedType = $moduleModel->getSharedType($userId);
				if ($sharedType === 'selectedusers') {
					$currentUserModel = Users_Record_Model::getCurrentUserModel();
					$sharedUserIds = Calendar_Module_Model::getCaledarSharedUsers($userId);
					if (!array_key_exists($currentUserModel->id, $sharedUserIds)) {
						$loadUrl = $listViewUrl;
					}
				} else if ($sharedType === 'private') {
					$loadUrl = $listViewUrl;
				}
			}
		}
		header("Location: $loadUrl");
	}

	/**
	 * Function to save record
	 * @param <Vtiger_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request) {
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			if($relatedModule->getName() == 'Events'){
				$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
			}
			$relatedRecordId = $recordModel->getId();

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
		return $recordModel;
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if(!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');
            //Due to dependencies on the activity_reminder api in Activity.php(5.x)
            $_REQUEST['mode'] = 'edit';
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('mode', '');
		}

		$fieldModelList = $moduleModel->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$fieldValue = $request->get($fieldName, null);
            // For custom time fields in Calendar, it was not converting to db insert format(sending as 10:00 AM/PM)
            $fieldDataType = $fieldModel->getFieldDataType();
            if($fieldDataType == 'time'){
				$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
            }
            // End
			if($fieldValue !== null) {
				if(!is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		}

		//Start Date and Time values
		$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('time_start'));
		$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('date_start')." ".$startTime);
		list($startDate, $startTime) = explode(' ', $startDateTime);

		$recordModel->set('date_start', $startDate);
		$recordModel->set('time_start', $startTime);

		//End Date and Time values
		$endTime = $request->get('time_end');
		$endDate = Vtiger_Date_UIType::getDBInsertedValue($request->get('due_date'));

		if ($endTime) {
			$endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($endTime);
			$endDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('due_date')." ".$endTime);
			list($endDate, $endTime) = explode(' ', $endDateTime);
		}

		$recordModel->set('time_end', $endTime);
		$recordModel->set('due_date', $endDate);

		$activityType = $request->get('activitytype');
		if(empty($activityType)) {
			$recordModel->set('activitytype', 'Task');
			$recordModel->set('visibility', 'Private');
		}

		//Due to dependencies on the older code
		$setReminder = $request->get('set_reminder');
		if($setReminder) {
			$_REQUEST['set_reminder'] = 'Yes';
		} else {
			$_REQUEST['set_reminder'] = 'No';
		}

		$time = strtotime($request->get('due_date'))-strtotime($request->get('date_start'));
		$hours = (float)$time/3600;
		$minutes = ((float)$hours-(int)$hours)*60;
		$recordModel->set('duration_hours', (int)$hours);
		$recordModel->set('duration_minutes', $minutes);

		return $recordModel;
	}
}
