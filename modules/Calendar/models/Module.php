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

/**
 * Calendar Module Model Class
 */
class Calendar_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function returns the default view for the Calendar module
	 * @return string
	 */
	public function getDefaultViewName()
	{
		return $this->getCalendarViewName();
	}

	/**
	 * Function returns the calendar view name
	 * @return string
	 */
	public function getCalendarViewName()
	{
		return 'Calendar';
	}

	/**
	 *  Function returns the url for Calendar view
	 * @return string
	 */
	public function getCalendarViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getCalendarViewName();
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return boolean - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/**
	 * Function returns the URL for creating Events
	 * @return string
	 */
	public function getCreateEventRecordUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getEditViewName() . '&mode=Events';
	}

	/**
	 * Function returns the URL for creating Task
	 * @return string
	 */
	public function getCreateTaskRecordUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getEditViewName() . '&mode=Calendar';
	}

	/**
	 * Function to get list of field for summary view
	 * @return <Array> empty array
	 */
	public function getSummaryViewFieldsList()
	{
		return [];
	}

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams)
	{
		$linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
		$links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = [
			[
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_CALENDAR_VIEW',
				'linkurl' => $this->getCalendarViewUrl(),
				'linkicon' => '',
			],
			/*
			  array(
			  'linktype' => 'SIDEBARLINK',
			  'linklabel' => 'LBL_SHARED_CALENDAR',
			  'linkurl' => $this->getSharedCalendarViewUrl(),
			  'linkicon' => '',
			  ), */
			[
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			],
		];
		if ($linkParams['ACTION'] == 'Calendar' && AppConfig::module('Calendar', 'SHOW_LIST_BUTTON')) {
			$quickLinks[] = [
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_CALENDAR_LIST',
				'linkurl' => 'javascript:Calendar_CalendarView_Js.getInstanceByView().goToRecordsList("' . $this->getListViewUrl() . '&viewname=All");',
				'linkicon' => '',
			];
		}
		foreach ($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		$quickWidgets = [];
		$quickWidgetsRight = [];

		if ($linkParams['ACTION'] == 'Calendar') {
			$quickWidgetsRight[] = array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'Activity Type',
				'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getActivityType',
				'linkicon' => ''
			);
			$quickWidgetsRight[] = array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_USERS',
				'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getUsersList',
				'linkicon' => ''
			);
			$quickWidgetsRight[] = array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_GROUPS',
				'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getGroupsList',
				'linkicon' => ''
			);
		}

		if ($linkParams['ACTION'] == 'SharedCalendar') {
			$quickWidgets[] = array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_ADDED_CALENDARS',
				'linkurl' => 'module=' . $this->get('name') . '&view=ViewTypes&mode=getSharedUsersList',
				'linkicon' => ''
			);
		}

		$quickWidgets[] = array(
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_RECENTLY_MODIFIED',
			'linkurl' => 'module=' . $this->get('name') . '&view=IndexAjax&mode=showActiveRecords',
			'linkicon' => ''
		);

		foreach ($quickWidgets as $quickWidget) {
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickWidget);
		}
		foreach ($quickWidgetsRight as $quickWidgetRight) {
			$links['SIDEBARWIDGETRIGHT'][] = Vtiger_Link_Model::getInstanceFromValues($quickWidgetRight);
		}

		return $links;
	}

	/**
	 * Function returns the url that shows Calendar Import result
	 * @return string url
	 */
	public function getImportResultUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=ImportResult';
	}

	/**
	 * Function to get export query
	 * @return string query;
	 */
	public function getExportQuery($focus = '', $where = '')
	{
		return (new App\Db\Query())->select(['vtiger_activity.*', 'vtiger_crmentity.description', 'assigned_user_id' => 'vtiger_crmentity.smownerid', 'vtiger_activity_reminder.reminder_time'])
				->from('vtiger_activity')
				->innerJoin('vtiger_crmentity', 'vtiger_activity.activityid = vtiger_crmentity.crmid')
				->leftJoin('vtiger_activity_reminder', 'vtiger_activity_reminder.activity_id = vtiger_activity.activityid')
				->where(['vtiger_crmentity.deleted' => 0, 'vtiger_crmentity.smownerid' => App\User::getCurrentUserId()]);
	}

	/**
	 * Function to set event fields for export
	 */
	public function setEventFieldsForExport()
	{
		$moduleFields = array_flip($this->getColumnFieldMapping());

		$keysToReplace = array('taskpriority');
		$keysValuesToReplace = array('taskpriority' => 'priority');

		foreach ($moduleFields as $fieldName => $fieldValue) {
			$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $this);
			if ($fieldName != 'id' && $fieldModel->getPermissions()) {
				if (!in_array($fieldName, $keysToReplace)) {
					$eventFields[$fieldName] = 'yes';
				} else {
					$eventFields[$keysValuesToReplace[$fieldName]] = 'yes';
				}
			}
		}
		$this->set('eventFields', $eventFields);
	}

	/**
	 * Function to set todo fields for export
	 */
	public function setTodoFieldsForExport()
	{
		$moduleFields = array_flip($this->getColumnFieldMapping());

		$keysToReplace = array('taskpriority', 'activitystatus');
		$keysValuesToReplace = array('taskpriority' => 'priority', 'activitystatus' => 'status');

		foreach ($moduleFields as $fieldName => $fieldValue) {
			$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $this);
			if ($fieldName != 'id' && $fieldModel->getPermissions()) {
				if (!in_array($fieldName, $keysToReplace)) {
					$todoFields[$fieldName] = 'yes';
				} else {
					$todoFields[$keysValuesToReplace[$fieldName]] = 'yes';
				}
			}
		}
		$this->set('todoFields', $todoFields);
	}

	/**
	 * Function to get the url to view Details for the module
	 * @return string - url
	 */
	public function getDetailViewUrl($id)
	{
		return 'index.php?module=Calendar&view=' . $this->getDetailViewName() . '&record=' . $id;
	}

	/**
	 * To get the lists of sharedids
	 * @param $id --  user id
	 * @returns <Array> $sharedids
	 */
	public static function getSharedUsersOfCurrentUser($id)
	{
		$db = PearDatabase::getInstance();
		$query = "SELECT vtiger_users.first_name,vtiger_users.last_name, vtiger_users.id as userid
			FROM vtiger_sharedcalendar RIGHT JOIN vtiger_users ON vtiger_sharedcalendar.userid=vtiger_users.id and status= 'Active'
			WHERE sharedid=? || (vtiger_users.status='Active' && vtiger_users.calendarsharedtype='public' && vtiger_users.id <> ?);";
		$result = $db->pquery($query, array($id, $id));
		$rows = $db->num_rows($result);

		$userIds = Array();
		for ($i = 0; $i < $rows; $i++) {
			$id = $db->query_result($result, $i, 'userid');
			$userName = $db->query_result($result, $i, 'first_name') . ' ' . $db->query_result($result, $i, 'last_name');
			$userIds[$id] = $userName;
		}

		return $sharedids[$id] = $userIds;
	}

	/**
	 * To get the lists of sharedids and colors
	 * @param $id --  user id
	 * @returns <Array> $sharedUsers
	 */
	public static function getSharedUsersInfoOfCurrentUser($id)
	{
		$db = PearDatabase::getInstance();

		$query = "SELECT shareduserid,color,visible FROM vtiger_shareduserinfo where userid = ?";
		$result = $db->pquery($query, array($id));
		$rows = $db->num_rows($result);

		$sharedUsers = Array();
		for ($i = 0; $i < $rows; $i++) {
			$sharedUserId = $db->query_result($result, $i, 'shareduserid');
			$color = $db->query_result($result, $i, 'color');
			$visible = $db->query_result($result, $i, 'visible');
			$sharedUsers[$sharedUserId] = array('visible' => $visible, 'color' => $color);
		}

		return $sharedUsers;
	}

	/**
	 * To get the lists of sharedids and colors
	 * @param $id --  user id
	 * @returns <Array> $sharedUsers
	 */
	public static function getCalendarViewTypes($id)
	{
		$db = PearDatabase::getInstance();

		$query = "SELECT * FROM vtiger_calendar_user_activitytypes 
			INNER JOIN vtiger_calendar_default_activitytypes on vtiger_calendar_default_activitytypes.id=vtiger_calendar_user_activitytypes.defaultid 
			WHERE vtiger_calendar_user_activitytypes.userid=? && vtiger_calendar_default_activitytypes.active = ?";
		$result = $db->pquery($query, array($id, 1));
		$rows = $db->num_rows($result);

		$calendarViewTypes = Array();
		for ($i = 0; $i < $rows; $i++) {
			$activityTypes = $db->query_result_rowdata($result, $i);
			$moduleInstance = vtlib\Module::getInstance($activityTypes['module']);
			$fieldInstance = vtlib\Field::getInstance($activityTypes['fieldname'], $moduleInstance);
			if ($fieldInstance) {
				$fieldLabel = $fieldInstance->label;
			} else {
				$fieldLabel = $activityTypes['fieldname'];
			}
			if ($activityTypes['visible'] == '1') {
				$calendarViewTypes['visible'][] = array('module' => $activityTypes['module'], 'fieldname' => $activityTypes['fieldname'], 'fieldlabel' => $fieldLabel, 'visible' => $activityTypes['visible'], 'color' => $activityTypes['color']);
			} else {
				$calendarViewTypes['invisible'][] = array('module' => $activityTypes['module'], 'fieldname' => $activityTypes['fieldname'], 'fieldlabel' => $fieldLabel, 'visible' => $activityTypes['visible'], 'color' => $activityTypes['color']);
			}
		}
		return $calendarViewTypes;
	}

	/**
	 *  Function returns the url for Shared Calendar view
	 * @return string
	 */
	public function getSharedCalendarViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=SharedCalendar';
	}

	/**
	 * Function to delete shared users
	 * @param type $currentUserId
	 */
	public function deleteSharedUsers($currentUserId)
	{
		$db = PearDatabase::getInstance();
		$delquery = "DELETE FROM vtiger_sharedcalendar WHERE userid=?";
		$db->pquery($delquery, array($currentUserId));
	}

	/**
	 * Function to insert shared users
	 * @param type $currentUserId
	 * @param type $sharedIds
	 */
	public function insertSharedUsers($currentUserId, $sharedIds, $sharedType = false)
	{
		$db = PearDatabase::getInstance();
		foreach ($sharedIds as $sharedId) {
			if ($sharedId != $currentUserId) {
				$sql = "INSERT INTO vtiger_sharedcalendar VALUES (?,?)";
				$db->pquery($sql, array($currentUserId, $sharedId));
			}
		}
	}

	/**
	 * Function to get Alphabet Search Field
	 */
	public function getAlphabetSearchField()
	{
		return 'subject';
	}

	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Calendar_Record_Model
	 */
	public function getRecentRecords($limit = 10)
	{
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$deletedCondition = parent::getDeletedRecordCondition();
		$nonAdminQuery .= Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());

		$query = 'SELECT * FROM vtiger_crmentity ';
		if ($nonAdminQuery) {
			$query .= " INNER JOIN vtiger_activity ON vtiger_crmentity.crmid = vtiger_activity.activityid " . $nonAdminQuery;
		}
		$query .= ' WHERE setype=? && %s && modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = [$this->getName(), $currentUserModel->id, $limit];
		$query = sprintf($query, $deletedCondition);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);
		$recentRecords = [];
		for ($i = 0; $i < $noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

	/**
	 * Function returns Calendar Reminder record models
	 * @return \Calendar_Record_Model[]
	 */
	public static function getCalendarReminder($allReminder = false)
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$activityReminder = $currentUserModel->getCurrentUserActivityReminderInSeconds();
		$recordModels = [];
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission('Calendar');
		$permissionToSendEmail = $permission && AppConfig::main('isActiveSendingMails') && Users_Privileges_Model::isPermitted('OSSMail');
		if (!empty($activityReminder)) {
			$currentTime = time();
			$time = date('Y-m-d H:i:s', strtotime("+$activityReminder seconds", $currentTime));

			$query = (new \App\Db\Query())
				->select('recordid')
				->from('vtiger_activity_reminder_popup')
				->innerJoin('vtiger_activity', 'vtiger_activity_reminder_popup.recordid = vtiger_activity.activityid')
				->innerJoin('vtiger_crmentity', 'vtiger_activity_reminder_popup.recordid = vtiger_crmentity.crmid')
				->distinct()
				->limit(20);
			if ($allReminder) {
				$query->where(['or', ['vtiger_activity_reminder_popup.status' => 0], ['vtiger_activity_reminder_popup.status' => 2]]);
			} else {
				$query->where(['vtiger_activity_reminder_popup.status' => 0]);
			}
			$query->andWhere(['vtiger_crmentity.smownerid' => $currentUserModel->getId(), 'vtiger_crmentity.deleted' => 0, 'vtiger_activity.status' => Calendar_Module_Model::getComponentActivityStateLabel('current')]);
			$query->andWhere(['<=', 'vtiger_activity_reminder_popup.datetime', $time])->orderBy(['vtiger_activity_reminder_popup.datetime' => SORT_DESC]);

			$dataReader = $query->createCommand()->query();
			while ($recordId = $dataReader->readColumn(0)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Calendar');
				$link = $recordModel->get('link');
				if ($link && $permissionToSendEmail) {
					$url = "index.php?module=OSSMail&view=compose&mod=" . vtlib\Functions::getCRMRecordType($link) . "&record=$link";
					$recordModel->set('mailUrl', "<a href='$url' class='btn btn-info' target='_blank'><span class='glyphicon glyphicon-envelope icon-white'></span>&nbsp;&nbsp;" . vtranslate('LBL_SEND_MAIL') . "</a>");
				}
				$recordModels[] = $recordModel;
			}
		}
		return $recordModels;
	}

	/**
	 * Function gives fields based on the type
	 * @param string $type - field type
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getFieldsByType($type)
	{
		$restrictedField = array('picklist' => array('activitystatus', 'visibility', 'duration_minutes'));

		if (!is_array($type)) {
			$type = array($type);
		}
		$fields = $this->getFields();
		$fieldList = [];
		foreach ($fields as $field) {
			$fieldType = $field->getFieldDataType();
			if (in_array($fieldType, $type)) {
				$fieldName = $field->getName();
				if ($fieldType == 'picklist' && in_array($fieldName, $restrictedField[$fieldType])) {
					
				} else {
					$fieldList[$fieldName] = $field;
				}
			}
		}
		return $fieldList;
	}

	/**
	 * Function returns Settings Links
	 * @return Array
	 */
	public function getSettingLinks()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = [];

		if ($currentUserModel->isAdminUser()) {
			$settingLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_FIELDS',
				'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule=' . $this->getName(),
				'linkicon' => Vtiger_Theme::getImagePath('LayoutEditor.gif')
			);

			$settingLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
				'linkurl' => 'index.php?parent=Settings&module=Picklist&view=Index&source_module=' . $this->getName(),
				'linkicon' => ''
			);
		}
		return $settingLinks;
	}

	/**
	 * Function to get orderby sql from orderby field
	 */
	public function getOrderBySql($orderBy)
	{
		if ($orderBy == 'status') {
			return $orderBy;
		}
		return parent::getOrderBySql($orderBy);
	}

	public static function getCalendarTypes()
	{
		$calendarConfig = ['Task'];
		$eventConfig = App\Fields\Picklist::getPickListValues('activitytype');
		if (is_array($eventConfig)) {
			$calendarConfig = array_merge($calendarConfig, $eventConfig);
		}
		return $calendarConfig;
	}

	public static function getCalendarState($data = [])
	{
		if ($data) {
			$activityStatus = $data['activitystatus'];
			if (in_array($activityStatus, Calendar_Module_Model::getComponentActivityStateLabel('history'))) {
				return false;
			}

			$dueDateTime = $data['due_date'] . ' ' . $data['time_end'];
			$startDateTime = $data['date_start'] . ' ' . $data['time_start'];
			$dates = ['start' => $startDateTime, 'end' => $dueDateTime, 'current' => null];

			foreach ($dates as $key => $date) {
				$date = new DateTimeField($date);
				$userFormatedString = $date->getDisplayDate();
				$timeFormatedString = $date->getDisplayTime();
				$dBFomatedDate = DateTimeField::convertToDBFormat($userFormatedString);
				$dates[$key] = strtotime($dBFomatedDate . " " . $timeFormatedString);
			}
			$activityStatusLabels = Calendar_Module_Model::getComponentActivityStateLabel();
			$state = $activityStatusLabels['not_started'];
			if ($dates['end'] > $dates['current'] && $dates['start'] < $dates['current']) {
				$state = $activityStatusLabels['in_realization'];
			} elseif ($dates['end'] > $dates['current']) {
				$state = $activityStatusLabels['not_started'];
			} elseif ($dates['end'] < $dates['current']) {
				$state = $activityStatusLabels['overdue'];
			}
			return $state;
		}
		return false;
	}

	/**
	 * The function gets the labels for a given status field 
	 * @param string $key
	 * @return <Array> 
	 */
	public static function getComponentActivityStateLabel($key = '')
	{
		$pickListValues = App\Fields\Picklist::getPickListValues('activitystatus');
		if (!is_array($pickListValues)) {
			return [];
		}
		$componentsActivityState = [];
		foreach ($pickListValues AS $value) {
			switch ($value) {
				case "PLL_PLANNED":
					$componentsActivityState['not_started'] = $value;
					break;
				case "PLL_IN_REALIZATION":
					$componentsActivityState['in_realization'] = $value;
					break;
				case "PLL_COMPLETED":
					$componentsActivityState['completed'] = $value;
					break;
				case "PLL_POSTPONED":
					$componentsActivityState['postponed'] = $value;
					break;
				case "PLL_OVERDUE":
					$componentsActivityState['overdue'] = $value;
					break;
				case "PLL_CANCELLED":
					$componentsActivityState['cancelled'] = $value;
					break;
			}
		}
		if ($key == 'current') {
			$componentsActivityState = ['PLL_PLANNED', 'PLL_IN_REALIZATION', 'PLL_OVERDUE'];
		} elseif ($key == 'history') {
			$componentsActivityState = ['PLL_COMPLETED', 'PLL_POSTPONED', 'PLL_CANCELLED'];
		} elseif ($key) {
			return $componentsActivityState[$key];
		}
		return $componentsActivityState;
	}
}
