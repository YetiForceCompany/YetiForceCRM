<?php
/**
 * CalDav calendar class file.
 *
 * @package   Integrations
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav;

use Sabre\VObject;

/**
 * Calendar class.
 */
class Calendar
{
	/**
	 * Record model instance.
	 *
	 * @var \Vtiger_Record_Model[]
	 */
	private $record = [];
	/**
	 * VCalendar object.
	 *
	 * @var \Sabre\VObject\Component\VCalendar
	 */
	private $vcalendar;

	/**
	 * Delete calendar event by crm id.
	 *
	 * @param int $id
	 *
	 * @throws \yii\db\Exception
	 */
	public static function deleteByCrmId(int $id)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = (new \App\Db\Query())->select(['id'])->from('dav_calendarobjects')->where(['crmid' => $id])->createCommand()->query();
		$dbCommand->delete('dav_calendarobjects', ['crmid' => $id])->execute();
		while ($calendarId = $dataReader->readColumn(0)) {
			static::addChange($calendarId, $id . '.vcf', 3);
		}
		$dataReader->close();
	}

	/**
	 * Add change to calendar.
	 *
	 * @param int    $calendarId
	 * @param string $uri
	 * @param int    $operation
	 *
	 * @throws \yii\db\Exception
	 */
	public static function addChange(int $calendarId, string $uri, int $operation)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$calendar = static::getCalendar($calendarId);
		$dbCommand->insert('dav_calendarchanges', [
			'uri' => $uri,
			'synctoken' => (int) $calendar['synctoken'],
			'calendarid' => $calendarId,
			'operation' => $operation
		])->execute();
		$dbCommand->update('dav_calendars', [
			'synctoken' => ((int) $calendar['synctoken']) + 1
		], ['id' => $calendarId])
		->execute();
	}

	/**
	 * Get calendar.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getCalendar(int $id)
	{
		return (new \App\Db\Query())->from('dav_calendars')->where(['id' => $id])->one();
	}

	public static function getCrmInstanceById(int $recordId)
	{
		$instance = new self();
		$instance->record = \Vtiger_Record_Model::getInstanceById($recordId, 'Calendar');
		return $instance;
	}

	public static function getCrmCleanInstance()
	{
		$instance = new self();
		$instance->record = \Vtiger_Record_Model::getCleanInstance('Calendar');
		return $instance;
	}

	/**
	 * Create a class instance by crm id.
	 *
	 * @param int    $record
	 * @param string $uid
	 *
	 * @return bool
	 */
	public function getByRecordId(int $record, string $uid)
	{
		\App\Log::trace($record, __METHOD__);
		if ($record) {
			$this->record[$uid] = \Vtiger_Record_Model::getInstanceById($record, 'Calendar');
		}
		return $this->getByRecordInstance()[$uid] ?? false;
	}

	/**
	 * Create a class instance from vcalendar content.
	 *
	 * @param string $content
	 *
	 * @return \App\Integrations\Dav\Calendar
	 */
	public static function loadFromContent(string $content)
	{
		$instance = new self();
		$instance->vcalendar = VObject\Reader::read($content);
		return $instance;
	}

	/**
	 * Get record instance.
	 *
	 * @return \Vtiger_Record_Model[]
	 */
	public function getRecordInstance()
	{
		foreach ($this->vcalendar->getBaseComponents() as $component) {
			$type = (string) $component->name;
			if ($type === 'VTODO' || $type === 'VEVENT') {
				$this->parseComponent($component);
			}
		}
		return $this->record;
	}

	/**
	 * Parse component.
	 *
	 * @param \Sabre\VObject\Component $component
	 */
	public function parseComponent(VObject\Component $component)
	{
		$uid = (string) $component->UID;
		if (isset($this->record[$uid])) {
			$recordModel = $this->record[$uid];
		} else {
			$recordModel = $this->record[$uid] = \Vtiger_Record_Model::getCleanInstance('Calendar');
		}
		$this->parseText('subject', 'SUMMARY', $recordModel, $component);
		$this->parseText('location', 'LOCATION', $recordModel, $component);
		$this->parseText('description', 'DESCRIPTION', $recordModel, $component);
		$this->parseStatus($recordModel, $component);
		$this->parsePriority($recordModel, $component);
		$this->parseVisibility($recordModel, $component);
		$this->parseState($recordModel, $component);
		$this->parseType($recordModel, $component);
		$this->parseDateTime($recordModel, $component);
	}

	/**
	 * Parse simple text.
	 *
	 * @param string                   $fieldName
	 * @param string                   $davName
	 * @param \Vtiger_Record_Model     $recordModel
	 * @param \Sabre\VObject\Component $component
	 */
	private function parseText(string $fieldName, string $davName, \Vtiger_Record_Model $recordModel, VObject\Component $component)
	{
		$value = \str_replace([
			'-::~:~::~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~::~:~::-'
		], '', \App\Purifier::purify((string) $component->$davName));
		if ($length = $recordModel->getField($fieldName)->get('maximumlength')) {
			$value = \App\TextParser::textTruncate($value, $length, false);
		}
		$recordModel->set($fieldName, \trim($value));
	}

	/**
	 * Parse status.
	 *
	 * @param \Vtiger_Record_Model     $recordModel
	 * @param \Sabre\VObject\Component $component
	 */
	private function parseStatus(\Vtiger_Record_Model $recordModel, VObject\Component $component)
	{
		$davValue = null;
		if (isset($component->STATUS)) {
			$davValue = strtoupper($component->STATUS->getValue());
		}
		if ((string) $component->name === 'VEVENT') {
			$values = [
				'TENTATIVE' => 'PLL_PLANNED',
				'CANCELLED' => 'PLL_CANCELLED',
				'CONFIRMED' => 'PLL_COMPLETED',
			];
		} else {
			$values = [
				'NEEDS-ACTION' => 'PLL_PLANNED',
				'IN-PROCESS' => 'PLL_IN_REALIZATION',
				'CANCELLED' => 'PLL_CANCELLED',
				'COMPLETED' => 'PLL_COMPLETED',
			];
		}
		$value = reset($values);
		if ($davValue && isset($values[$davValue])) {
			$value = $values[$davValue];
		}
		$recordModel->set('activitystatus', $value);
	}

	/**
	 * Parse visibility.
	 *
	 * @param \Vtiger_Record_Model     $recordModel
	 * @param \Sabre\VObject\Component $component
	 */
	private function parseVisibility(\Vtiger_Record_Model $recordModel, VObject\Component $component)
	{
		$davValue = null;
		$value = 'Private';
		if (isset($component->CLASS)) {
			$davValue = strtoupper($component->CLASS->getValue());
			$values = [
				'PUBLIC' => 'Public',
				'PRIVATE' => 'Private'
			];
			if ($davValue && isset($values[$davValue])) {
				$value = $values[$davValue];
			}
		}
		$recordModel->set('visibility', $value);
	}

	/**
	 * Parse state.
	 *
	 * @param \Vtiger_Record_Model     $recordModel
	 * @param \Sabre\VObject\Component $component
	 */
	private function parseState(\Vtiger_Record_Model $recordModel, VObject\Component $component)
	{
		$davValue = null;
		$value = '';
		if (isset($component->TRANSP)) {
			$davValue = strtoupper($component->TRANSP->getValue());
			$values = [
				'OPAQUE' => 'PLL_OPAQUE',
				'TRANSPARENT' => 'PLL_TRANSPARENT'
			];
			if ($davValue && isset($values[$davValue])) {
				$value = $values[$davValue];
			}
		}
		$recordModel->set('state', $value);
	}

	/**
	 * Parse priority.
	 *
	 * @param \Vtiger_Record_Model     $recordModel
	 * @param \Sabre\VObject\Component $component
	 */
	private function parsePriority(\Vtiger_Record_Model $recordModel, VObject\Component $component)
	{
		$davValue = null;
		$value = 'Medium';
		if (isset($component->PRIORITY)) {
			$davValue = strtoupper($component->PRIORITY->getValue());
			$values = [
				1 => 'High',
				5 => 'Medium',
				9 => 'Low',
			];
			if ($davValue && isset($values[$davValue])) {
				$value = $values[$davValue];
			}
		}
		$recordModel->set('taskpriority', $value);
	}

	/**
	 * Parse type.
	 *
	 * @param \Vtiger_Record_Model     $recordModel
	 * @param \Sabre\VObject\Component $component
	 */
	private function parseType(\Vtiger_Record_Model $recordModel, VObject\Component $component)
	{
		$recordModel->set('activitytype', (string) $component->name === 'VTODO' ? 'Task' : 'Meeting');
	}

	/**
	 * Parse date time.
	 *
	 * @param \Vtiger_Record_Model     $recordModel
	 * @param \Sabre\VObject\Component $component
	 */
	private function parseDateTime(\Vtiger_Record_Model $recordModel, VObject\Component $component)
	{
		$allDay = 0;
		$endHasTime = $startHasTime = false;
		$endField = ((string) $component->name) === 'VEVENT' ? 'DTEND' : 'DUE';
		if (isset($component->DTSTART)) {
			$davStart = VObject\DateTimeParser::parse($component->DTSTART);
			$dateStart = $davStart->format('Y-m-d');
			$timeStart = $davStart->format('H:i:s');
			$startHasTime = $component->DTSTART->hasTime();
		} else {
			$davStart = VObject\DateTimeParser::parse($component->DTSTAMP);
			$dateStart = $davStart->format('Y-m-d');
			$timeStart = $davStart->format('H:i:s');
		}
		if (isset($component->$endField)) {
			$davEnd = VObject\DateTimeParser::parse($component->$endField);
			$endHasTime = $component->$endField->hasTime();
			$dueDate = $davEnd->format('Y-m-d');
			$timeEnd = $davEnd->format('H:i:s');
			if (!$endHasTime) {
				$endTime = strtotime('-1 day', strtotime("$dueDate $timeEnd"));
				$dueDate = date('Y-m-d', $endTime);
				$timeEnd = date('H:i:s', $endTime);
			}
		} else {
			$endTime = strtotime('+1 day', strtotime("$dateStart $timeStart"));
			$dueDate = date('Y-m-d', $endTime);
			$timeEnd = date('H:i:s', $endTime);
		}
		if (!$startHasTime && !$endHasTime) {
			$allDay = 1;
			$currentUser = \App\User::getCurrentUserModel();
			$timeStart = $currentUser->getDetail('start_hour') . ':00';
			$timeEnd = $currentUser->getDetail('end_hour') . ':00';
		}
		$recordModel->set('allday', $allDay);
		$recordModel->set('date_start', $dateStart);
		$recordModel->set('due_date', $dueDate);
		$recordModel->set('time_start', $timeStart);
		$recordModel->set('time_end', $timeEnd);
	}
}
