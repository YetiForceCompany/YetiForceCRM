<?php

/**
 * Calendar model class.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Vtiger_Calendar_Model class.
 */
abstract class Vtiger_Calendar_Model extends App\Base
{
	/**
	 * @var string Module name
	 */
	public $moduleName;
	/**
	 * @var \Vtiger_Module_Model Module model
	 */
	public $module;

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
	 * Get module model.
	 *
	 * @return \Vtiger_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			$this->module = Vtiger_Module_Model::getInstance($this->getModuleName());
		}
		return $this->module;
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
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['SIDEBARWIDGET'], $linkParams)['SIDEBARWIDGET'] ?? [];
		$links[] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_USERS',
			'linkurl' => "module={$this->getModuleName()}&view=RightPanel&mode=getUsersList",
			'linkclass' => 'js-calendar__filter--users'
		]);
		$links[] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_GROUPS',
			'linkurl' => "module={$this->getModuleName()}&view=RightPanel&mode=getGroupsList",
			'linkclass' => 'js-calendar__filter--groups',
		]);
		return $links;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name.
	 *
	 * @param mixed id or name of the module
	 * @param mixed $moduleName
	 */
	public static function getInstance(string $moduleName)
	{
		$className = Vtiger_Loader::getComponentClassName('Model', 'Calendar', $moduleName);
		$handler = new $className();
		$handler->moduleName = $moduleName;
		return $handler;
	}

	/**
	 * Get public holidays for rendering them on the calendar.
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
	 * Gest query.
	 *
	 * @return \App\Db\Query
	 */
	abstract public function getQuery();

	/**
	 * Function to get records.
	 *
	 * @return array
	 */
	abstract public function getEntity();

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

	/**
	 * Update event.
	 *
	 * @param int    $recordId
	 * @param string $date
	 * @param array  $delta
	 *
	 * @return bool
	 */
	public function updateEvent(int $recordId, string $date, array $delta)
	{
		$start = DateTimeField::convertToDBTimeZone($date, \App\User::getCurrentUserModel(), false);
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->getModuleName());
			if ($success = $recordModel->isEditable()) {
				$end = $this->changeDateTime($recordModel->get('due_date') . ' ' . $recordModel->get('time_end'), $delta);
				$recordModel->set('date_start', $start->format('Y-m-d'));
				$recordModel->set('time_start', $start->format('H:i:s'));
				$recordModel->set('due_date', $end['date']);
				$recordModel->set('time_end', $end['time']);
				$recordModel->save();
				$success = true;
			}
		} catch (Exception $e) {
			\App\Log::error($e->__toString());
			$success = false;
		}
		return $success;
	}

	/**
	 * Modify date.
	 *
	 * @param string $datetime
	 * @param array  $delta
	 *
	 * @return string[]
	 */
	public function changeDateTime($datetime, $delta)
	{
		$date = new DateTime($datetime);
		if (0 != $delta['days']) {
			$date = $date->modify('+' . $delta['days'] . ' days');
		}
		if (0 != $delta['hours']) {
			$date = $date->modify('+' . $delta['hours'] . ' hours');
		}
		if (0 != $delta['minutes']) {
			$date = $date->modify('+' . $delta['minutes'] . ' minutes');
		}
		return ['date' => $date->format('Y-m-d'), 'time' => $date->format('H:i:s')];
	}
}
