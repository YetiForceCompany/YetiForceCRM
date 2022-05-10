<?php

/**
 * Calendar Model Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    YetiForce S.A.
 */
class Calendar_Calendar_Model extends Vtiger_Calendar_Model
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
		if (!empty($this->get('user')) && isset($this->get('user')['selectedIds'][0])) {
			$selectedUsers = $this->get('user');
			$selectedIds = $selectedUsers['selectedIds'];
			if ('all' !== $selectedIds[0]) {
				$conditions[] = ['vtiger_crmentity.smownerid' => $selectedIds];
				$subQuery = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => $selectedIds]);
				$conditions[] = ['vtiger_crmentity.crmid' => $subQuery];
			}
			if (isset($selectedUsers['excludedIds']) && 'all' === $selectedIds[0]) {
				$conditions[] = ['not in', 'vtiger_crmentity.smownerid', $selectedUsers['excludedIds']];
			}
		}
		if ($conditions) {
			$query->andWhere(array_merge(['or'], $conditions));
		}
		$query->orderBy('vtiger_activity.date_start,vtiger_activity.time_start');

		return $query;
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
		$editForm = \App\Config::module('Calendar', 'SHOW_EDIT_FORM');
		$dataReader = $this->getQuery()->createCommand()->query();
		$colors = \App\Fields\Picklist::getColors('activitytype', false);
		while ($row = $dataReader->read()) {
			$item = [];
			if ($editForm && $moduleModel->getRecordFromArray($row)->setId($row['id'])->isEditable()) {
				$item['url'] = 'index.php?module=' . $this->getModuleName() . '&view=EventForm&record=' . $row['id'];
			} else {
				$item['url'] = 'index.php?module=' . $this->getModuleName() . '&view=ActivityState&record=' . $row['id'];
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
			$item['borderColor'] = $colors[$row['activitytype']] ?? '';
			$item['className'] = 'js-popover-tooltip--record ownerCBg_' . $row['assigned_user_id'];
			$return[] = $item;
		}
		$dataReader->close();
		return $return;
	}

	/**
	 * Gets number of events.
	 *
	 * @return array
	 */
	public function getEntityCount(): array
	{
		$colors = \App\Fields\Picklist::getColors('activitytype', false);
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
			$activityType = $record['activitytype'];

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
					$dateKey = $date . '__' . $activityType;

					$return[$dateKey]['allDay'] = true;
					$return[$dateKey]['start'] = $date;
					if (isset($return[$dateKey]['title'])) {
						++$return[$dateKey]['title'];
					} else {
						$return[$dateKey]['title'] = 1;
					}
					$return[$dateKey]['label'] = \App\Language::translate($activityType, $this->getModuleName());
					$return[$dateKey]['className'] = 'fc-draggable picklistCBg_Calendar_activitytype_' . $activityType;
					$return[$dateKey]['borderColor'] = $colors[$record['activitytype']] ?? '';
					$return[$dateKey]['type'] = 'widget';
					$return[$dateKey]['activityType'] = $activityType;
					$return[$dateKey]['url'] = 'index.php?module=' . $this->getModuleName() . '&view=List&entityState=Active';
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

	/** {@inheritdoc} */
	public function getSideBarLinks($linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['SIDEBARWIDGET'], $linkParams)['SIDEBARWIDGET'] ?? [];
		$request = \App\Request::init();
		$historyUsers = $request->has('user') ? $request->get('user') : [];
		$links[] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_USERS',
			'linkclass' => 'js-users-form usersForm ',
			'template' => 'Filters/Users.tpl',
			'filterData' => Vtiger_CalendarRightPanel_Model::getUsersList($this->moduleName),
			'historyUsers' => $historyUsers,
		]);
		$links[] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_GROUPS',
			'linkclass' => 'js-group-form groupForm',
			'template' => 'Filters/Groups.tpl',
			'filterData' => Vtiger_CalendarRightPanel_Model::getGroupsList($this->moduleName),
			'historyUsers' => $historyUsers,
		]);
		return $links;
	}

	/** {@inheritdoc} */
	public function updateEvent(int $recordId, string $start, string $end, App\Request $request): bool
	{
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->getModuleName());
			if ($success = $recordModel->isEditable()) {
				$start = DateTimeField::convertToDBTimeZone($start);
				$recordModel->set('date_start', $start->format('Y-m-d'));
				$end = DateTimeField::convertToDBTimeZone($end);
				$recordModel->set('due_date', $end->format('Y-m-d'));
				if ($request->getBoolean('allDay')) {
					$recordModel->set('allday', 1);
				} else {
					$recordModel->set('time_start', $start->format('H:i:s'));
					$recordModel->set('time_end', $end->format('H:i:s'));
					$recordModel->set('allday', 0);
				}
				$recordModel->save();
				$success = true;
			}
		} catch (Exception $e) {
			\App\Log::error($e->__toString());
			$success = false;
		}
		return $success;
	}
}
