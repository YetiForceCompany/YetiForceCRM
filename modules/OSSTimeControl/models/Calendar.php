<?php

/**
 * OSSTimeControl calendar model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSTimeControl_Calendar_Model extends App\Base
{

	/**
	 * Function to get records
	 * @return array
	 */
	public function getEntity()
	{
		$moduleName = 'OSSTimeControl';
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->setFields(['id', 'date_start', 'time_start', 'time_end', 'due_date', 'timecontrol_type', 'name', 'assigned_user_id']);
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
					['<=', new \yii\db\Expression("CONCAT(vtiger_osstimecontrol.date_start, ' ', vtiger_osstimecontrol.time_start)"), $dbEndDateTime]
				],
				[
					'and',
					['>=', new \yii\db\Expression("CONCAT(vtiger_osstimecontrol.due_date, ' ', vtiger_osstimecontrol.time_end)"), $dbStartDateTime],
					['<=', new \yii\db\Expression("CONCAT(vtiger_osstimecontrol.due_date, ' ', vtiger_osstimecontrol.time_end)"), $dbEndDateTime]
				],
				[
					'and',
					['<', 'vtiger_osstimecontrol.date_start', $dbStartDate],
					['>', 'vtiger_osstimecontrol.due_date', $dbEndDate]
				]
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
			$item['title'] = $record['name'];
			$item['url'] = 'index.php?module=OSSTimeControl&view=Detail&record=' . $record['id'];
			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString . ' ' . $dateTimeComponents[1];
			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ', $userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['end'] = $dataBaseDateFormatedString . ' ' . $dateTimeComponents[1];
			$item['className'] = ' userCol_' . $record['assigned_user_id'] . ' calCol_' . $record['timecontrol_type'];
			$result[] = $item;
		}
		return $result;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name
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
	 * Function to get type of calendars
	 * @return string[]
	 */
	public static function getCalendarTypes()
	{
		return \App\Fields\Picklist::getPickListValues('timecontrol_type');
	}
}
