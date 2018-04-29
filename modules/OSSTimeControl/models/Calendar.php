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
		$moduleName = 'OSSTimeControl';
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->setFields(['id', 'date_start', 'time_start', 'time_end', 'due_date', 'timecontrol_type', 'name', 'assigned_user_id', 'osstimecontrol_status', 'sum_time', 'osstimecontrol_no', 'process', 'link', 'subprocess', 'linkextend']);
		$query = $queryGenerator->createQuery();
		if ($this->get('start') && $this->get('end')) {
			$dbStartDateOject = DateTimeField::convertToDBTimeZone($this->get('start'), null, false);
			$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
			$dbStartDate = $dbStartDateOject->format('Y-m-d');
			$dbEndDateObject = DateTimeField::convertToDBTimeZone($this->get('end'), null, false);
			$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
			$dbEndDate = $dbEndDateObject->format('Y-m-d');
			$query->andWhere([
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
				],
			]);
		}
		if (!$this->isEmpty('types')) {
			$query->andWhere(['vtiger_osstimecontrol.timecontrol_type' => $this->get('types')]);
		}
		if (!$this->isEmpty('user')) {
			$query->andWhere(['vtiger_crmentity.smownerid' => $this->get('user')]);
		}
		$dataReader = $query->createCommand()->query();
		$result = [];
		while ($record = $dataReader->read()) {
			$item = [];
			$item['id'] = $record['id'];
			$item['title'] = \App\Purifier::encodeHtml($record['name']);
			$item['url'] = 'index.php?module=OSSTimeControl&view=Detail&record=' . $record['id'];
			$item['status'] = \App\Language::translate($record['osstimecontrol_status'], 'OSSTimeControl');
			$item['type'] = \App\Language::translate($record['timecontrol_type'], 'OSSTimeControl');
			$item['number'] = $record['osstimecontrol_no'];
			//Relation
			if ($record['link']) {
				$item['link'] = $record['link'];
				$item['linkl'] = \App\Record::getLabel($record['link']);
				// / migoi
				$item['linkm'] = \App\Record::getType($record['link']);
			}
			//Process
			if ($record['process']) {
				$item['process'] = $record['process'];
				$item['procl'] = \App\Record::getLabel($record['process']);
				$item['procm'] = \App\Record::getType($record['process']);
			}
			//Subprocess
			if ($record['subprocess']) {
				$item['subprocess'] = $record['subprocess'];
				$item['subprocl'] = \App\Record::getLabel($record['subprocess']);
				$item['subprocm'] = \App\Record::getType($record['subprocess']);
			}
			//linkextend
			if ($record['linkextend']) {
				$item['linkextend'] = $record['linkextend'];
				$item['linkexl'] = \App\Record::getLabel($record['linkextend']);
				$item['linkexm'] = \App\Record::getType($record['linkextend']);
			}
			$item['totalTime'] = \App\Fields\Time::formatToHourText($record['sum_time'], 'short');
			$item['smownerid'] = \App\Fields\Owner::getLabel($record['assigned_user_id']);
			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString . ' ' . $dateTimeComponents[1];
			$item['start_display'] = $userDateTimeString;
			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['end'] = $dataBaseDateFormatedString . ' ' . $dateTimeComponents[1];
			$item['end_display'] = $userDateTimeString;
			$item['className'] = ' ownerCBg_' . $record['assigned_user_id'] . ' picklistCBr_OSSTimeControl_timecontrol_type_' . $record['timecontrol_type'];
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
