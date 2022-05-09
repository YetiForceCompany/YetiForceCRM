<?php

 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * Calendar Module Model Class.
 */
class Calendar_Module_Model extends Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $allowTypeChange = false;

	/**
	 * Function returns the default view for the Calendar module.
	 *
	 * @return string
	 */
	public function getDefaultViewName()
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
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getDefaultViewName();
	}

	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/** {@inheritdoc} */
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
		if (isset($linkParams['ACTION']) && 'Calendar' === $linkParams['ACTION'] && App\Config::module('Calendar', 'SHOW_LIST_BUTTON')) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_CALENDAR_LIST',
				'linkurl' => 'javascript:Calendar_Calendar_Js.goToRecordsList("' . $this->getListViewUrl() . '");',
				'linkicon' => 'far fa-calendar-minus',
			]);
		}
		if ($this->isPermitted('Kanban') && \App\Utils\Kanban::getBoards($this->getName(), true)) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_VIEW_KANBAN',
				'linkurl' => 'index.php?module=' . $this->getName() . '&view=Kanban',
				'linkicon' => 'yfi yfi-kanban',
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
	 * @param mixed $focus
	 * @param mixed $where
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
				if (!\in_array($fieldName, $keysToReplace)) {
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
				if (!\in_array($fieldName, $keysToReplace)) {
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
	 * @param mixed $id
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
	public static function getCalendarReminder()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$activityReminder = $currentUserModel->getCurrentUserActivityReminderInSeconds();
		$recordModels = [];
		if (!empty($activityReminder)) {
			$time = date('Y-m-d H:i:s', strtotime("+$activityReminder seconds"));
			$query = (new \App\Db\Query())
				->select(['recordid', 'vtiger_activity_reminder_popup.datetime'])
				->from('vtiger_activity_reminder_popup')
				->innerJoin('vtiger_activity', 'vtiger_activity_reminder_popup.recordid = vtiger_activity.activityid')
				->innerJoin('vtiger_crmentity', 'vtiger_activity_reminder_popup.recordid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.smownerid' => $currentUserModel->getId(), 'vtiger_crmentity.deleted' => 0, 'vtiger_activity.status' => self::getComponentActivityStateLabel('current')])
				->andWhere(['or', ['and', ['vtiger_activity_reminder_popup.status' => Calendar_Record_Model::REMNDER_POPUP_ACTIVE], ['<=', 'vtiger_activity_reminder_popup.datetime', $time]], ['and', ['vtiger_activity_reminder_popup.status' => Calendar_Record_Model::REMNDER_POPUP_WAIT], ['<=', 'vtiger_activity_reminder_popup.datetime', date('Y-m-d H:i:s')]]])
				->orderBy(['vtiger_activity_reminder_popup.datetime' => SORT_DESC])
				->distinct()
				->limit(\App\Config::module('Calendar', 'maxNumberCalendarNotifications', 20));
			$dataReader = $query->createCommand()->query();
			while ($recordId = $dataReader->readColumn(0)) {
				$recordModels[] = Vtiger_Record_Model::getInstanceById($recordId, 'Calendar');
			}
		}
		return $recordModels;
	}

	/** {@inheritdoc} */
	public function getFieldsByType($type, bool $active = false): array
	{
		$restrictedField = ['picklist' => ['activitystatus', 'visibility', 'duration_minutes']];
		if (!\is_array($type)) {
			$type = [$type];
		}
		$fields = $this->getFields();
		$fieldList = [];
		foreach ($fields as $field) {
			$fieldType = $field->getFieldDataType();
			if (\in_array($fieldType, $type)) {
				$fieldName = $field->getName();
				if ('picklist' == $fieldType && \in_array($fieldName, $restrictedField[$fieldType])) {
				} else {
					$fieldList[$fieldName] = $field;
				}
			}
		}
		return $fieldList;
	}

	/** {@inheritdoc} */
	public function getSettingLinks(): array
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = [];
		if ($currentUserModel->isAdminUser()) {
			$settingLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_FIELDS',
				'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule=' . $this->getName(),
				'linkicon' => 'adminIcon-modules-fields',
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
	 *
	 * @param mixed $orderBy
	 */
	public function getOrderBySql($orderBy)
	{
		if ('status' == $orderBy) {
			return $orderBy;
		}
		return parent::getOrderBySql($orderBy);
	}

	public static function getCalendarTypes()
	{
		return App\Fields\Picklist::getValuesName('activitytype');
	}

	public static function getCalendarState($data = [])
	{
		if ($data) {
			$activityStatus = $data['activitystatus'];
			if (\in_array($activityStatus, self::getComponentActivityStateLabel('history'))) {
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
		if (!\is_array($pickListValues)) {
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
				default:
					break;
			}
		}
		if ('current' == $key) {
			$componentsActivityState = ['PLL_PLANNED', 'PLL_IN_REALIZATION', 'PLL_OVERDUE'];
		} elseif ('history' == $key) {
			$componentsActivityState = ['PLL_COMPLETED', 'PLL_POSTPONED', 'PLL_CANCELLED'];
		} elseif ($key) {
			return $componentsActivityState[$key];
		}
		return $componentsActivityState;
	}

	/**
	 * Import calendar rekords from ICS.
	 *
	 * @param string $filePath
	 *
	 * @throws \Exception
	 *
	 * @return array
	 */
	public function importICS(string $filePath)
	{
		$userId = \App\User::getCurrentUserRealId();
		$lastImport = new ICalLastImport();
		$lastImport->clearRecords($userId);
		$eventModule = 'Events';
		$todoModule = 'Calendar';
		$totalCount = $skipCount = [$eventModule => 0, $todoModule => 0];
		$calendar = \App\Integrations\Dav\Calendar::loadFromContent(file_get_contents($filePath));
		foreach ($calendar->getRecordInstance() as $recordModel) {
			$recordModel->set('assigned_user_id', $userId);
			$recordModel->save();
			$calendar->recordSaveAttendee($recordModel);
			if ('VEVENT' === (string) $calendar->getComponent()->name) {
				$module = $eventModule;
			} else {
				$module = $todoModule;
			}
			if ($recordModel->getId()) {
				++$totalCount[$module];
				$lastImport = new ICalLastImport();
				$lastImport->setFields(['userid' => $userId, 'entitytype' => $this->getName(), 'crmid' => $recordModel->getId()]);
				$lastImport->save();
			} else {
				++$skipCount[$module];
			}
		}
		return ['events' => $totalCount[$eventModule] - $skipCount[$eventModule], 'skipped_events' => $skipCount[$eventModule], 'task' => $totalCount[$todoModule] - $skipCount[$todoModule], 'skipped_task' => $skipCount[$todoModule]];
	}

	/** {@inheritdoc} */
	public function getLayoutTypeForQuickCreate(): string
	{
		return 'standard';
	}
}
