<?php

/**
 * Occurrences calendar model class.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Occurrences_Calendar_Model extends App\Base
{
	public $moduleName = 'Occurrences';

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
		$queryGenerator->setFields(['id', 'date_start', 'date_end', 'occurrences_type', 'occurrences_status', 'topic', 'assigned_user_id']);
		if (!$this->isEmpty('types')) {
			$queryGenerator->addNativeCondition(['u_yf_occurrences.topic' => $this->get('types')]);
		}
		$components = ['PLL_ARCHIVED', 'PLL_CANCELLED'];
		if ('current' == $this->get('time')) {
			$queryGenerator->addCondition('occurrences_status', implode('##', $components), 'n');
		} elseif ('history' == $this->get('time')) {
			$queryGenerator->addCondition('occurrences_status', implode('##', $components), 'e');
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
					['>=', 'u_yf_occurrences.date_start', $dbStartDateTime],
					['<=', 'u_yf_occurrences.date_start', $dbEndDateTime],
				],
				[
					'and',
					['>=', 'u_yf_occurrences.date_end', $dbStartDateTime],
					['<=', 'u_yf_occurrences.date_end', $dbEndDateTime],
				],
				[
					'and',
					['<', 'u_yf_occurrences.date_start', $dbStartDate],
					['>', 'u_yf_occurrences.date_end', $dbEndDate],
				]
			]);
		}

		$query = $queryGenerator->createQuery();
		if ($this->has('filters')) {
			foreach ($this->get('filters') as $filter) {
				$filterClassName = Vtiger_Loader::getComponentClassName('CalendarFilter', $filter['name'], $this->getModuleName());
				$filterInstance = new $filterClassName();
				if ($filterInstance->checkPermissions() && $conditions = $filterInstance->getCondition($filter['value'])) {
					$query->andWhere($conditions);
				}
			}
		}
		$conditions = [];
		$currentUser = App\User::getCurrentUserModel();
		if (1 === $currentUser->getRoleInstance()->get('clendarallorecords')) {
			$subQuery = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => $currentUser->getId()]);
			$conditions[] = ['vtiger_crmentity.crmid' => $subQuery];
		}
		if (!empty($this->get('user'))) {
			$conditions[] = ['vtiger_crmentity.smownerid' => $this->get('user')];
		}
		if ($conditions) {
			$query->andWhere(array_merge(['or'], $conditions));
		}
		$query->orderBy(['u_yf_occurrences.date_start' => SORT_ASC]);

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
	 * Function to get records.
	 *
	 * @return array
	 */
	public function getEntity()
	{
		$dataReader = $this->getQuery()->createCommand()->query();
		$result = [];
		$isSummaryViewSupported = Vtiger_Module_Model::getInstance($this->getModuleName())->isSummaryViewSupported();
		while ($record = $dataReader->read()) {
			$item = [];
			$item['id'] = $record['id'];
			$item['title'] = \App\Purifier::encodeHtml($record['topic']);

			$dateTimeInstance = new DateTimeField($record['date_start']);
			$item['start'] = DateTimeField::convertToUserTimeZone($record['date_start'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['start_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$dateTimeInstance = new DateTimeField($record['date_end']);
			$item['end'] = DateTimeField::convertToUserTimeZone($record['date_end'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['end_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$item['className'] = 'js-popover-tooltip--record ownerCBg_' . $record['assigned_user_id'] . ' picklistCBr_Occurrences_occurrences_type_' . $record['occurrences_type'];
			if ($isSummaryViewSupported) {
				$item['url'] = 'index.php?module=' . $this->getModuleName() . '&view=QuickDetailModal&record=' . $record['id'];
				$item['className'] .= ' js-show-modal';
			} else {
				$item['url'] = 'index.php?module=Occurrences&view=Detail&record=' . $record['id'];
			}
			$result[] = $item;
		}
		$dataReader->close();
		return $result;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name.
	 *
	 * @param mixed id or name of the module
	 */
	public static function getInstance()
	{
		return new self();
	}

	/**
	 * Function to get type of calendars.
	 *
	 * @return string[]
	 */
	public static function getCalendarTypes()
	{
		return \App\Fields\Picklist::getValuesName('occurrences_type');
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
	 * Gets entity count.
	 *
	 * @return array
	 */
	public function getEntityCount()
	{
		$currentUser = \App\User::getCurrentUserModel();
		$startDate = DateTimeField::convertToDBTimeZone($this->get('start'));
		$startDate = strtotime($startDate->format('Y-m-d H:i:s'));
		$endDate = DateTimeField::convertToDBTimeZone($this->get('end'));
		$endDate = strtotime($endDate->format('Y-m-d H:i:s'));
		$dataReader = $this->getQuery()
			->createCommand()
			->query();
		$return = [];
		while ($record = $dataReader->read()) {
			$activitytype = $record['occurrences_status'];

			$dateFormat = \App\Fields\DateTime::formatToDisplay($record['date_start']);
			$dateTimeComponents = explode(' ', $dateFormat);
			$startDateFormated = \App\Fields\Date::sanitizeDbFormat($dateTimeComponents[0], $currentUser->getDetail('date_format'));

			$dateFormat = \App\Fields\DateTime::formatToDisplay($record['date_start']);
			$dateTimeComponents = explode(' ', $dateFormat);
			$endDateFormated = \App\Fields\Date::sanitizeDbFormat($dateTimeComponents[0], $currentUser->getDetail('date_format'));

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
					$return[$date]['event'][$activitytype]['className'] = '  fc-draggable picklistCBg_Occurrences_occurrences_status_' . $activitytype;
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
		$currentUser = \App\User::getCurrentUserModel();
		$startDate = DateTimeField::convertToDBTimeZone($this->get('start'));
		$startDate = strtotime($startDate->format('Y-m-d H:i:s'));
		$endDate = DateTimeField::convertToDBTimeZone($this->get('end'));
		$endDate = strtotime($endDate->format('Y-m-d H:i:s'));
		$dataReader = $this->getQuery()->createCommand()->query();
		$return = [];
		while ($record = $dataReader->read()) {
			$dateFormat = \App\Fields\DateTime::formatToDisplay($record['date_start']);
			$dateTimeComponents = explode(' ', $dateFormat);
			$startDateFormated = \App\Fields\Date::sanitizeDbFormat($dateTimeComponents[0], $currentUser->getDetail('date_format'));

			$dateFormat = \App\Fields\DateTime::formatToDisplay($record['date_start']);
			$dateTimeComponents = explode(' ', $dateFormat);
			$endDateFormated = \App\Fields\Date::sanitizeDbFormat($dateTimeComponents[0], $currentUser->getDetail('date_format'));

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
}
