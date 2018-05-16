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
 * Calendar Module Model Class.
 */
class Calendar_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function returns the default view for the Calendar module.
	 *
	 * @return string
	 */
	public function getDefaultViewName()
	{
		return $this->getCalendarViewName();
	}

	/**
	 * Function returns the calendar view name.
	 *
	 * @return string
	 */
	public function getCalendarViewName()
	{
		return 'Calendar';
	}

	/**
	 *  Function returns the url for Calendar view.
	 *
	 * @return string
	 */
	public function getCalendarViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getCalendarViewName();
	}

	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/**
	 * Function returns the URL for creating Events.
	 *
	 * @return string
	 */
	public function getCreateEventRecordUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getEditViewName() . '&mode=events';
	}

	/**
	 * Function returns the URL for creating Task.
	 *
	 * @return string
	 */
	public function getCreateTaskRecordUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getEditViewName() . '&mode=calendar';
	}

	/**
	 * Function to get list of field for summary view.
	 *
	 * @return <Array> empty array
	 */
	public function getSummaryViewFieldsList()
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getId(), ['SIDEBARLINK', 'SIDEBARWIDGET'], $linkParams);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_CALENDAR_VIEW',
				'linkurl' => $this->getCalendarViewUrl(),
				'linkicon' => 'fas fa-calendar-alt',
		]);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => 'fas fa-list',
		]);
		if (isset($linkParams['ACTION']) && $linkParams['ACTION'] === 'Calendar' && AppConfig::module('Calendar', 'SHOW_LIST_BUTTON')) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'SIDEBARLINK',
					'linklabel' => 'LBL_CALENDAR_LIST',
					'linkurl' => 'javascript:Calendar_CalendarView_Js.getInstanceByView().goToRecordsList("' . $this->getListViewUrl() . '&viewname=All");',
					'linkicon' => 'far fa-calendar-minus',
			]);
		}
		if ($linkParams['ACTION'] === 'Calendar') {
			$links['SIDEBARWIDGETRIGHT'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'SIDEBARWIDGETRIGHT',
					'linklabel' => 'Activity Type',
					'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getActivityType',
					'linkicon' => '',
			]);
			$links['SIDEBARWIDGETRIGHT'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'SIDEBARWIDGETRIGHT',
					'linklabel' => 'LBL_USERS',
					'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getUsersList',
					'linkicon' => '',
			]);
			$links['SIDEBARWIDGETRIGHT'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'SIDEBARWIDGETRIGHT',
					'linklabel' => 'LBL_GROUPS',
					'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getGroupsList',
					'linkicon' => '',
			]);
		}

		return $links;
	}

	/**
	 * Function returns the url that shows Calendar Import result.
	 *
	 * @return string url
	 */
	public function getImportResultUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=ImportResult';
	}

	/**
	 * Function to get export query.
	 *
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
	 * Function to set event fields for export.
	 */
	public function setEventFieldsForExport()
	{
		$keysToReplace = ['taskpriority'];
		$keysValuesToReplace = ['taskpriority' => 'priority'];
		foreach ($this->getFields() as $fieldName => $fieldModel) {
			if ($fieldModel->getPermissions()) {
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
	 * Function to set todo fields for export.
	 */
	public function setTodoFieldsForExport()
	{
		$keysToReplace = ['taskpriority', 'activitystatus'];
		$keysValuesToReplace = ['taskpriority' => 'priority', 'activitystatus' => 'status'];
		foreach ($this->getFields() as $fieldName => $fieldModel) {
			if ($fieldModel->getPermissions()) {
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
	 * Function to get the url to view Details for the module.
	 *
	 * @return string - url
	 */
	public function getDetailViewUrl($id)
	{
		return 'index.php?module=Calendar&view=' . $this->getDetailViewName() . '&record=' . $id;
	}

	/**
	 * Function to get Alphabet Search Field.
	 */
	public function getAlphabetSearchField()
	{
		return 'subject';
	}

	/**
	 * Function returns Calendar Reminder record models.
	 *
	 * @return \Calendar_Record_Model[]
	 */
	public static function getCalendarReminder($allReminder = false)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$activityReminder = $currentUserModel->getCurrentUserActivityReminderInSeconds();
		$recordModels = [];
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission('Calendar');
		$permissionToSendEmail = $permission && AppConfig::main('isActiveSendingMails') && \App\Privilege::isPermitted('OSSMail');
		if (!empty($activityReminder)) {
			$currentTime = time();
			$time = date('Y-m-d H:i:s', strtotime("+$activityReminder seconds", $currentTime));

			$query = (new \App\Db\Query())
				->select(['recordid', 'vtiger_activity_reminder_popup.datetime'])
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
			$query->andWhere(['vtiger_crmentity.smownerid' => $currentUserModel->getId(), 'vtiger_crmentity.deleted' => 0, 'vtiger_activity.status' => self::getComponentActivityStateLabel('current')]);
			$query->andWhere(['<=', 'vtiger_activity_reminder_popup.datetime', $time])->orderBy(['vtiger_activity_reminder_popup.datetime' => SORT_DESC]);

			$dataReader = $query->createCommand()->query();
			while ($recordId = $dataReader->readColumn(0)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Calendar');
				$link = $recordModel->get('link');
				if ($link && $permissionToSendEmail) {
					$url = 'index.php?module=OSSMail&view=Compose&mod=' . \App\Record::getType($link) . "&record=$link";
					$recordModel->set('mailUrl', "<a href='$url' class='btn btn-info' target='_blank'><span class='fas fa-envelope icon-white'></span>&nbsp;&nbsp;" . \App\Language::translate('LBL_SEND_MAIL') . '</a>');
				}
				$recordModels[] = $recordModel;
			}
		}

		return $recordModels;
	}

	/**
	 * Function gives fields based on the type.
	 *
	 * @param string $type - field type
	 *
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getFieldsByType($type)
	{
		$restrictedField = ['picklist' => ['activitystatus', 'visibility', 'duration_minutes']];

		if (!is_array($type)) {
			$type = [$type];
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
	 * Function returns Settings Links.
	 *
	 * @return array
	 */
	public function getSettingLinks()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = [];

		if ($currentUserModel->isAdminUser()) {
			$settingLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_FIELDS',
				'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule=' . $this->getName(),
				'linkicon' => 'adminIcon-triggers',
			];

			$settingLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
				'linkurl' => 'index.php?parent=Settings&module=Picklist&view=Index&source_module=' . $this->getName(),
				'linkicon' => 'adminIcon-fields-picklists',
			];
		}

		return $settingLinks;
	}

	/**
	 * Function to get orderby sql from orderby field.
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
		$eventConfig = App\Fields\Picklist::getValuesName('activitytype');
		if (is_array($eventConfig)) {
			$calendarConfig = array_merge($calendarConfig, $eventConfig);
		}

		return $calendarConfig;
	}

	public static function getCalendarState($data = [])
	{
		if ($data) {
			$activityStatus = $data['activitystatus'];
			if (in_array($activityStatus, self::getComponentActivityStateLabel('history'))) {
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
				$dates[$key] = strtotime($dBFomatedDate . ' ' . $timeFormatedString);
			}
			$activityStatusLabels = self::getComponentActivityStateLabel();
			if (!empty($data['activitystatus']) && isset($activityStatusLabels[$data['activitystatus']])) {
				$state = $activityStatusLabels[$data['activitystatus']];
			} else {
				$state = $activityStatusLabels['not_started'];
				if ($dates['end'] > $dates['current'] && $dates['start'] < $dates['current']) {
					$state = $activityStatusLabels['in_realization'];
				} elseif ($dates['end'] > $dates['current']) {
					$state = $activityStatusLabels['not_started'];
				} elseif ($dates['end'] < $dates['current']) {
					$state = $activityStatusLabels['overdue'];
				}
			}

			return $state;
		}

		return false;
	}

	/**
	 * The function gets the labels for a given status field.
	 *
	 * @param string $key
	 *
	 * @return <Array>
	 */
	public static function getComponentActivityStateLabel($key = '')
	{
		$pickListValues = App\Fields\Picklist::getValuesName('activitystatus');
		if (!is_array($pickListValues)) {
			return [];
		}
		$componentsActivityState = [];
		foreach ($pickListValues as $value) {
			switch ($value) {
				case 'PLL_PLANNED':
					$componentsActivityState['not_started'] = $value;
					break;
				case 'PLL_IN_REALIZATION':
					$componentsActivityState['in_realization'] = $value;
					break;
				case 'PLL_COMPLETED':
					$componentsActivityState['completed'] = $value;
					break;
				case 'PLL_POSTPONED':
					$componentsActivityState['postponed'] = $value;
					break;
				case 'PLL_OVERDUE':
					$componentsActivityState['overdue'] = $value;
					break;
				case 'PLL_CANCELLED':
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
