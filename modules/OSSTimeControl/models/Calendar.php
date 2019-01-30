<?php

/**
 * OSSTimeControl calendar model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_Calendar_Model extends App\Base
{
	/**
	 * Function to get records.
	 *
	 * @return array
	 */
	public function getEntity()
	{
		$queryGenerator = new App\QueryGenerator('OSSTimeControl');
		$queryGenerator->setFields(['id', 'date_start', 'time_start', 'time_end', 'due_date', 'timecontrol_type', 'name', 'assigned_user_id']);
		if (!$this->isEmpty('types')) {
			$queryGenerator->addNativeCondition(['vtiger_osstimecontrol.timecontrol_type' => $this->get('types')]);
		}
		if (!$this->isEmpty('user')) {
			$queryGenerator->addNativeCondition(['vtiger_crmentity.smownerid' => $this->get('user')]);
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
					['>=', new \yii\db\Expression("CONCAT(vtiger_osstimecontrol.date_start, ' ', vtiger_osstimecontrol.time_start)"), $dbStartDateTime],
					['<=', new \yii\db\Expression("CONCAT(vtiger_osstimecontrol.date_start, ' ', vtiger_osstimecontrol.time_start)"), $dbEndDateTime],
				],
				[
					'and',
					['>=', new \yii\db\Expression("CONCAT(vtiger_osstimecontrol.due_date, ' ', vtiger_osstimecontrol.time_end)"), $dbStartDateTime],
					['<=', new \yii\db\Expression("CONCAT(vtiger_osstimecontrol.due_date, ' ', vtiger_osstimecontrol.time_end)"), $dbEndDateTime],
				],
				[
					'and',
					['<', 'vtiger_osstimecontrol.date_start', $dbStartDate],
					['>', 'vtiger_osstimecontrol.due_date', $dbEndDate],
				]
			]);
		}

		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$result = [];
		while ($record = $dataReader->read()) {
			$item = [];
			$item['id'] = $record['id'];
			$item['title'] = \App\Purifier::encodeHtml($record['name']);
			$item['url'] = 'index.php?module=OSSTimeControl&view=Detail&record=' . $record['id'];

			$dateTimeInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$item['start'] = DateTimeField::convertToUserTimeZone($record['date_start'] . ' ' . $record['time_start'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['start_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$dateTimeInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$item['end'] = DateTimeField::convertToUserTimeZone($record['due_date'] . ' ' . $record['time_end'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['end_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$item['className'] = 'js-popover-tooltip--record ownerCBg_' . $record['assigned_user_id'] . ' picklistCBr_OSSTimeControl_timecontrol_type_' . $record['timecontrol_type'];
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
		$instance = Vtiger_Cache::get('ossTimeControlModels', 'Calendar');
		if ($instance === false) {
			$instance = new self();
			Vtiger_Cache::set('ossTimeControlModels', 'Calendar', clone $instance);

			return $instance;
		} else {
			return clone $instance;
		}
	}

	/**
	 * Function to get type of calendars.
	 *
	 * @return string[]
	 */
	public static function getCalendarTypes()
	{
		return \App\Fields\Picklist::getValuesName('timecontrol_type');
	}
}
