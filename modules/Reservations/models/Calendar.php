<?php

/**
 * Reservations calendar model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Reservations_Calendar_Model extends \App\Base
{
	/**
	 * Function to get records.
	 *
	 * @return array
	 */
	public function getEntity()
	{
		$queryGenerator = new App\QueryGenerator('Reservations');
		$queryGenerator->setFields(['id', 'date_start', 'time_start', 'time_end', 'due_date', 'title', 'assigned_user_id']);
		if (!$this->isEmpty('types')) {
			$queryGenerator->addNativeCondition(['vtiger_reservations.type' => $this->get('types')]);
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
					['>=', new \yii\db\Expression("CONCAT(vtiger_reservations.date_start, ' ', vtiger_reservations.time_start)"), $dbStartDateTime],
					['<=', new \yii\db\Expression("CONCAT(vtiger_reservations.date_start, ' ', vtiger_reservations.time_start)"), $dbEndDateTime],
				],
				[
					'and',
					['>=', new \yii\db\Expression("CONCAT(vtiger_reservations.due_date, ' ', vtiger_reservations.time_end)"), $dbStartDateTime],
					['<=', new \yii\db\Expression("CONCAT(vtiger_reservations.due_date, ' ', vtiger_reservations.time_end)"), $dbEndDateTime],
				],
				[
					'and',
					['<', 'vtiger_reservations.date_start', $dbStartDate],
					['>', 'vtiger_reservations.due_date', $dbEndDate],
				]
			]);
		}

		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$result = [];
		while ($record = $dataReader->read()) {
			$item = [];
			$item['id'] = $record['id'];
			$item['title'] = \App\Purifier::encodeHtml($record['title']);
			$item['url'] = 'index.php?module=Reservations&view=Detail&record=' . $record['id'];

			$dateTimeInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$item['start'] = DateTimeField::convertToUserTimeZone($record['date_start'] . ' ' . $record['time_start'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['start_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$dateTimeInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$item['end'] = DateTimeField::convertToUserTimeZone($record['due_date'] . ' ' . $record['time_end'])->format('Y-m-d') . ' ' . $dateTimeInstance->getFullcalenderTime();
			$item['end_display'] = $dateTimeInstance->getDisplayDateTimeValue();

			$item['className'] = 'js-popover-tooltip--record ownerCBg_' . $record['assigned_user_id'];
			$result[] = $item;
		}
		$dataReader->close();
		return $result;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name.
	 */
	public static function getInstance()
	{
		$instance = Vtiger_Cache::get('reservationsModels', 'Calendar');
		if ($instance === false) {
			$instance = new self();
			Vtiger_Cache::set('reservationsModels', 'Calendar', clone $instance);

			return $instance;
		} else {
			return clone $instance;
		}
	}

	/**
	 * Function to get calendar types.
	 *
	 * @return string[]
	 */
	public static function getCalendarTypes()
	{
		$templateId = Vtiger_Field_Model::getInstance('type', Vtiger_Module_Model::getInstance('Reservations'))->getFieldParams();

		return (new App\Db\Query())->select(['tree', 'label'])->from('vtiger_trees_templates_data')
			->where(['templateid' => $templateId])
			->createCommand()->queryAllByGroup(0);
	}
}
