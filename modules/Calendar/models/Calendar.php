<?php

/**
 * Calendar Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    YetiForce.com
 */
class Calendar_Calendar_Model extends App\Base
{
	public $moduleName = 'Calendar';
	public $module;
	public $relationAcounts = [
		'Contacts' => ['vtiger_contactdetails', 'contactid', 'parentid'],
		'Project' => ['vtiger_project', 'projectid', 'linktoaccountscontacts'],
		'HelpDesk' => ['vtiger_troubletickets', 'ticketid', 'parent_id'],
		'ServiceContracts' => ['vtiger_servicecontracts', 'servicecontractsid', 'sc_related_to'],
	];

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			$this->module = Vtiger_Module_Model::getInstance($this->getModuleName());
		}
		return $this->module;
	}

	/**
	 * Get query.
	 *
	 * @return \App\Db\Query
	 */
	public function getQuery()
	{
		$queryGenerator = new App\QueryGenerator($this->getModuleName());
		if ($this->has('customFilter')) {
			$queryGenerator->initForCustomViewById($this->get('customFilter'));
		}
		$queryGenerator->setFields(array_keys($queryGenerator->getModuleFields()));
		$queryGenerator->setField('id');
		if ($types = $this->get('types')) {
			$queryGenerator->addCondition('activitytype', implode('##', $types), 'e');
		}
		switch ($this->get('time')) {
			case 'current':
				$queryGenerator->addCondition('activitystatus', implode('##', Calendar_Module_Model::getComponentActivityStateLabel('current')), 'e');
				break;
			case 'history':
				$queryGenerator->addCondition('activitystatus', implode('##', Calendar_Module_Model::getComponentActivityStateLabel('history')), 'e');
				break;
			default:
				break;
		}
		if (!empty($this->get('activitystatus'))) {
			$queryGenerator->addNativeCondition(['vtiger_activity.status' => $this->get('activitystatus')]);
		}
		if ($this->get('start') && $this->get('end')) {
			$dbStartDateOject = DateTimeField::convertToDBTimeZone($this->get('start'));
			$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
			$dbStartDate = $dbStartDateOject->format('Y-m-d');
			$dbEndDateObject = DateTimeField::convertToDBTimeZone($this->get('end'));
			$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
			$dbEndDate = $dbEndDateObject->format('Y-m-d');
			$queryGenerator->addNativeCondition([
				'or',
				[
					'and',
					['>=', new \yii\db\Expression("CONCAT(date_start, ' ', time_start)"), $dbStartDateTime],
					['<=', new \yii\db\Expression("CONCAT(date_start, ' ', time_start)"), $dbEndDateTime],
				],
				[
					'and',
					['>=', new \yii\db\Expression("CONCAT(due_date, ' ', time_end)"), $dbStartDateTime],
					['<=', new \yii\db\Expression("CONCAT(due_date, ' ', time_end)"), $dbEndDateTime],
				],
				[
					'and',
					['<', 'date_start', $dbStartDate],
					['>', 'due_date', $dbEndDate],
				],
			]);
		}
		$query = $queryGenerator->createQuery();
		if ($this->has('filters')) {
			foreach ($this->get('filters') as $filter) {
				$filterClassName = Vtiger_Loader::getComponentClassName('CalendarFilter', $filter['name'], 'Calendar');
				$filterInstance = new $filterClassName();
				if ($filterInstance->checkPermissions() && $conditions = $filterInstance->getCondition($filter['value'])) {
					$query->andWhere($conditions);
				}
			}
		}
		$conditions = [];
		if (!empty($this->get('user'))) {
			$conditions[] = ['vtiger_crmentity.smownerid' => $this->get('user')];
			$subQuery = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => $this->get('user')]);
			$conditions[] = ['vtiger_crmentity.crmid' => $subQuery];
		}
		if ($conditions) {
			$query->andWhere(array_merge(['or'], $conditions));
		}
		$query->orderBy('vtiger_activity.date_start,vtiger_activity.time_start');

		return $query;
	}

	/**
	 * Get records count for extended calendar left column.
	 *
	 * @return int|string
	 */
	public function getEntityRecordsCount()
	{
		return $this->getQuery()->count();
	}

	/**
	 * Get public holidays for rendenring them on the calendar.
	 *
	 * @return array
	 */
	public function getPublicHolidays()
	{
		$result = [];
		foreach (App\Fields\Date::getHolidays(DateTimeField::convertToDBTimeZone($this->get('start'))->format('Y-m-d'), DateTimeField::convertToDBTimeZone($this->get('end'))->format('Y-m-d')) as $holiday) {
			$item = [];
			$item['title'] = $holiday['name'];
			$item['type'] = $holiday['type'];
			$item['start'] = $holiday['date'];
			$item['rendering'] = 'background';
			if ('national' === $item['type']) {
				$item['color'] = '#FFAB91';
				$item['icon'] = 'fas fa-flag';
			} else {
				$item['color'] = '#81D4FA';
				$item['icon'] = 'fas fa-church';
			}
			$result[] = $item;
		}
		return $result;
	}

	/**
	 * Gets entity data.
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return array
	 */
	public function getEntity()
	{
		$return = [];
		$currentUser = \App\User::getCurrentUserModel();
		$moduleModel = Vtiger_Module_Model::getInstance($this->getModuleName());
		$extended = 'Extended' === App\Config::module('Calendar', 'CALENDAR_VIEW');
		$editForm = \App\Config::module('Calendar', 'SHOW_EDIT_FORM');
		$dataReader = $this->getQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$item = [];
			if ($extended) {
				if ($editForm && $moduleModel->getRecordFromArray($row, true)->setId($row['id'])->isEditable()) {
					$item['url'] = 'index.php?module=' . $this->getModuleName() . '&view=EventForm&record=' . $row['id'];
				} else {
					$item['url'] = 'index.php?module=' . $this->getModuleName() . '&view=ActivityState&record=' . $row['id'];
				}
			} else {
				$item['url'] = 'index.php?module=' . $this->getModuleName() . '&view=Detail&record=' . $row['id'];
			}
			$item['module'] = $this->getModuleName();
			$item['title'] = \App\Purifier::encodeHtml($row['subject']);
			$item['id'] = $row['id'];
			$item['set'] = 'Task' == $row['activitytype'] ? 'Task' : 'Event';
			$dateTimeFieldInstance = new DateTimeField($row['date_start'] . ' ' . $row['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getFullcalenderDateTimevalue();
			$startDateTimeDisplay = $dateTimeFieldInstance->getDisplayDateTimeValue();
			$startTimeDisplay = $dateTimeFieldInstance->getDisplayTime();
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$startTimeFormated = $dateTimeComponents[1];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$startDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->getDetail('date_format'));

			$dateTimeFieldInstance = new DateTimeField($row['due_date'] . ' ' . $row['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getFullcalenderDateTimevalue();
			$endDateTimeDisplay = $dateTimeFieldInstance->getDisplayDateTimeValue();
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$endTimeFormated = $dateTimeComponents[1];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$endDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->getDetail('date_format'));

			$item['allDay'] = 1 == $row['allday'];
			$item['start_date'] = $row['date_start'];
			if ($item['allDay']) {
				$item['start'] = $startDateFormated;
				$item['end'] = $endDateFormated;
			} else {
				$item['start'] = $startDateFormated . ' ' . $startTimeFormated;
				$item['end'] = $endDateFormated . ' ' . $endTimeFormated;
			}

			$item['start_display'] = $startDateTimeDisplay;
			$item['end_display'] = $endDateTimeDisplay;
			$item['hour_start'] = $startTimeDisplay;
			$item['className'] = 'js-popover-tooltip--record ownerCBg_' . $row['assigned_user_id'] . ' picklistCBr_Calendar_activitytype_' . \App\Colors::sanitizeValue($row['activitytype']);
			$return[] = $item;
		}
		$dataReader->close();
		return $return;
	}

	public function getEntityCount()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$startDate = DateTimeField::convertToDBTimeZone($this->get('start'));
		$startDate = strtotime($startDate->format('Y-m-d H:i:s'));
		$endDate = DateTimeField::convertToDBTimeZone($this->get('end'));
		$endDate = strtotime($endDate->format('Y-m-d H:i:s'));
		$dataReader = $this->getQuery()
			->createCommand()
			->query();
		$return = [];
		while ($record = $dataReader->read()) {
			$activitytype = $record['activitytype'];

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$startDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$endDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));

			$begin = new DateTime($startDateFormated);
			$end = new DateTime($endDateFormated);
			$end->modify('+1 day');
			$interval = DateInterval::createFromDateString('1 day');
			foreach (new DatePeriod($begin, $interval, $end) as $dt) {
				$date = strtotime($dt->format('Y-m-d'));
				if ($date >= $startDate && $date <= $endDate) {
					$date = date('Y-m-d', $date);

					$return[$date]['start'] = $date;
					$return[$date]['date'] = $date;
					if (isset($return[$date]['event'][$activitytype]['count'])) {
						++$return[$date]['event'][$activitytype]['count'];
					} else {
						$return[$date]['event'][$activitytype]['count'] = 1;
					}
					$return[$date]['event'][$activitytype]['className'] = '  fc-draggable picklistCBg_Calendar_activitytype_' . $activitytype;
					$return[$date]['event'][$activitytype]['label'] = \App\Language::translate($activitytype, $this->getModuleName());
					$return[$date]['type'] = 'widget';
				}
			}
		}
		$dataReader->close();

		return array_values($return);
	}

	/**
	 * Get entity count for year view.
	 *
	 * @return array
	 */
	public function getEntityYearCount()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$startDate = DateTimeField::convertToDBTimeZone($this->get('start'));
		$startDate = strtotime($startDate->format('Y-m-d H:i:s'));
		$endDate = DateTimeField::convertToDBTimeZone($this->get('end'));
		$endDate = strtotime($endDate->format('Y-m-d H:i:s'));
		$dataReader = $this->getQuery()
			->createCommand()
			->query();
		$return = [];
		while ($record = $dataReader->read()) {
			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$startDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			$endDateFormated = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));

			$begin = new DateTime($startDateFormated);
			$end = new DateTime($endDateFormated);
			$end->modify('+1 day');
			$interval = DateInterval::createFromDateString('1 day');
			foreach (new DatePeriod($begin, $interval, $end) as $dt) {
				$date = strtotime($dt->format('Y-m-d'));
				if ($date >= $startDate && $date <= $endDate) {
					$date = date('Y-m-d', $date);
					$return[$date]['date'] = $date;
					if (isset($return[$date]['count'])) {
						++$return[$date]['count'];
					} else {
						$return[$date]['count'] = 1;
					}
				}
			}
		}
		$dataReader->close();

		return array_values($return);
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name.
	 *
	 * @param mixed id or name of the module
	 */
	public static function getCleanInstance()
	{
		$instance = Vtiger_Cache::get('calendarModels', 'Calendar');
		if (false === $instance) {
			$instance = new self();
			Vtiger_Cache::set('calendarModels', 'Calendar', clone $instance);

			return $instance;
		}
		return clone $instance;
	}

	public static function getCalendarTypes()
	{
		return [
			'PLL_WORKING_TIME',
			'PLL_BREAK_TIME',
			'PLL_HOLIDAY',
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['SIDEBARWIDGET'], $linkParams)['SIDEBARWIDGET'] ?? [];
		if ('Extended' === App\Config::module('Calendar', 'CALENDAR_VIEW')) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_USERS',
				'linkurl' => "module={$this->getModuleName()}&view=RightPanelExtended&mode=getUsersList",
				'linkclass' => 'js-users-form usersForm '
			]);
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_GROUPS',
				'linkurl' => "module={$this->getModuleName()}&view=RightPanelExtended&mode=getGroupsList",
				'linkclass' => 'js-group-form groupForm',
			]);
		} else {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGETRIGHT',
				'linklabel' => 'Activity Type',
				'linkurl' => "module={$this->getModuleName()}&view=RightPanel&mode=getActivityType",
				'linkdata' => ['cache' => 'calendar-types', 'name' => 'types'],
				'linkclass' => 'js-calendar__filter--types',
			]);
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGETRIGHT',
				'linklabel' => 'LBL_USERS',
				'linkurl' => "module={$this->getModuleName()}&view=RightPanel&mode=getUsersList",
				'linkicon' => '',
				'linkclass' => 'js-calendar__filter--users',
			]);
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGETRIGHT',
				'linklabel' => 'LBL_GROUPS',
				'linkurl' => "module={$this->getModuleName()}&view=RightPanel&mode=getGroupsList",
				'linkicon' => '',
				'linkclass' => 'js-calendar__filter--groups',
			]);
		}
		return $links;
	}
}
