<?php

/**
 * Calendar export model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_rfc2445.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_components.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_properties.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/iCalendar_parameters.php');

class Calendar_ExportToIcal_Model extends Vtiger_Export_Model
{
	/**
	 * {@inheritdoc}
	 */
	protected $fileExtension = 'ics';

	/**
	 * {@inheritdoc}
	 */
	public function getExportContentType(): string
	{
		return 'text/calendar';
	}

	/**
	 * {@inheritdoc}
	 */
	public function exportData()
	{
		$this->moduleInstance->setEventFieldsForExport();
		$this->moduleInstance->setTodoFieldsForExport();
		parent::exportData();
	}

	/**
	 * {@inheritdoc}
	 */
	public function output($headers, $entries)
	{
		$timeZone = new IcalendarTimezone();
		$timeZoneId = explode('/', date_default_timezone_get());
		if (!empty($timeZoneId[1])) {
			$zoneId = $timeZoneId[1];
		} else {
			$zoneId = $timeZoneId[0];
		}
		$timeZone->addProperty('TZID', $zoneId);
		$timeZone->addProperty('TZOFFSETTO', date('O'));
		if (date('I') == 1) {
			$timeZone->addProperty('DAYLIGHTC', date('I'));
		} else {
			$timeZone->addProperty('STANDARDC', date('I'));
		}
		$myiCal = new Icalendar();
		$myiCal->addComponent($timeZone);
		foreach ($entries as $eventFields) {
			if ('Task' === $eventFields['activitytype']) {
				$iCalTask = $this->getIcalendarTodo($eventFields);
			} else {
				$iCalTask = $this->getIcalendarEvent($eventFields);
			}
			$myiCal->addComponent($iCalTask);
		}
		echo $myiCal->serialize();
	}

	/**
	 * Get IcalendarTodo component.
	 *
	 * @param array $eventFields
	 *
	 * @throws \Exception
	 *
	 * @return \IcalendarTodo
	 */
	private function getIcalendarTodo(array $eventFields): IcalendarTodo
	{
		$priorityMap = ['High' => '1', 'Medium' => '2', 'Low' => '3'];
		$temp = $this->moduleInstance->get('todoFields');
		foreach ($temp as $fieldName => $access) {
			if ($fieldName === 'priority') {
				$priorityval = $eventFields['taskpriority'];
				$icalZeroPriority = 0;
				if (array_key_exists($priorityval, $priorityMap)) {
					$temp[$fieldName] = $priorityMap[$priorityval];
				} else {
					$temp[$fieldName] = $icalZeroPriority;
				}
			} elseif ($fieldName === 'status') {
				$temp[$fieldName] = $eventFields['activitystatus'];
			} else {
				$temp[$fieldName] = $eventFields[$fieldName];
			}
		}
		$iCalTask = new IcalendarTodo();
		$iCalTask->assignValues($temp);
		return $iCalTask;
	}

	/**
	 * Get IcalendarEvent component.
	 *
	 * @param array $eventFields
	 *
	 * @throws \Exception
	 *
	 * @return \IcalendarEvent
	 */
	private function getIcalendarEvent(array $eventFields): IcalendarEvent
	{
		$priorityMap = ['High' => '1', 'Medium' => '2', 'Low' => '3'];
		$temp = $this->moduleInstance->get('eventFields');
		foreach ($temp as $fieldName => $access) {
			if ($fieldName === 'priority') {
				$priorityval = $eventFields['taskpriority'];
				$icalZeroPriority = 0;
				if (array_key_exists($priorityval, $priorityMap)) {
					$temp[$fieldName] = $priorityMap[$priorityval];
				} else {
					$temp[$fieldName] = $icalZeroPriority;
				}
			} else {
				$temp[$fieldName] = $eventFields[$fieldName];
			}
		}
		$temp['id'] = $eventFields['id'];
		$iCalTask = new IcalendarEvent();
		$iCalTask->assignValues($temp);
		$iCalAlarm = new IcalendarAlarm();
		$iCalAlarm->assignValues($temp);
		$iCalTask->addComponent($iCalAlarm);
		return $iCalTask;
	}
}
