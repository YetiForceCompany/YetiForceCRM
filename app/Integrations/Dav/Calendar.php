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
	private $records = [];
	/**
	 * Record data.
	 *
	 * @var \Vtiger_Record_Model
	 */
	private $record = [];
	/**
	 * VCalendar object.
	 *
	 * @var \Sabre\VObject\Component\VCalendar
	 */
	private $vcalendar;
	/**
	 * @var \Sabre\VObject\Component
	 */
	private $vcomponent;
	/**
	 * Sequence counter.
	 *
	 * @var int
	 */
	private $sequence = 0;
	/**
	 * Optimization for creating a time zone.
	 *
	 * @var bool
	 */
	private $createdTimeZone = false;

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

	public static function loadFromRecord(int $recordId)
	{
		$instance = new self();
		$instance->record[$recordId] = \Vtiger_Record_Model::getInstanceById($recordId, 'Calendar');
		(new \App\Db\Query())->from('dav_calendarobjects')->where(['calendarid' => $this->calendarId, 'crmid' => $this->record['crmid']])->one();
		//$instance->vcalendar
		return $instance;
	}

	/**
	 * Create empty instance.
	 *
	 * @return \App\Integrations\Dav\Calendar
	 */
	public static function createEmptyInstance()
	{
		$instance = new self();
		$instance->record = \Vtiger_Record_Model::getCleanInstance('Calendar');
		$instance->vcalendar = new VObject\Component\VCalendar();
		$instance->vcalendar->PRODID = '-//YetiForce//YetiForceCRM V' . \App\Version::get() . '//';
		return $instance;
	}

	/**
	 * Load record data.
	 *
	 * @param array $data
	 */
	public function loadFromArray(array $data)
	{
		$this->record->setData($data);
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
			$this->records[$uid] = \Vtiger_Record_Model::getInstanceById($record, 'Calendar');
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
		return $this->records;
	}

	/**
	 * Parse component.
	 *
	 * @param \Sabre\VObject\Component $component
	 */
	private function parseComponent(VObject\Component $component)
	{
		$uid = (string) $component->UID;
		if (isset($this->records[$uid])) {
			$recordModel = $this->records[$uid];
		} else {
			$recordModel = $this->records[$uid] = \Vtiger_Record_Model::getCleanInstance('Calendar');
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

	/**
	 * Get vcalendar serialized blob.
	 *
	 * @return string
	 */
	public function generate()
	{
		return $this->vcalendar->serialize();
	}

	/**
	 * Create calendar entry component.
	 */
	public function createComponent()
	{
		$componentType = $this->record->get('activitytype') === 'Task' ? 'VTODO' : 'VEVENT';
		$this->vcomponent = $this->vcalendar->createComponent($componentType);
		$this->vcomponent->UID = \str_replace('sabre-vobject', 'YetiForceCRM', (string) $this->vcomponent->UID);
		$this->updateComponent();
		$this->vcomponent->add($this->vcalendar->createProperty('SEQUENCE', $this->sequence));
		$this->sequence++;
		$this->vcalendar->add($this->vcomponent);
	}

	/**
	 * Update calendar entry component.
	 *
	 * @throws \Sabre\VObject\InvalidDataException
	 */
	public function updateComponent()
	{
		$this->createDateTime();
		$this->createText('subject', 'SUMMARY');
		$this->createText('location', 'LOCATION');
		$this->createText('description', 'DESCRIPTION');
		$this->createStatus();
		$this->createVisibility();
		$this->createState();
		$this->createPriority();
		if (empty($this->vcomponent->CREATED)) {
			$createdTime = new \DateTime();
			$createdTime->setTimezone(new \DateTimeZone('UTC'));
			$this->vcomponent->add($this->vcalendar->createProperty('CREATED', $createdTime));
		}
	}

	/**
	 * Create a text value for dav.
	 *
	 * @param string $fieldName
	 * @param string $davName
	 *
	 * @throws \Sabre\VObject\InvalidDataException
	 */
	private function createText(string $fieldName, string $davName)
	{
		$empty = $this->record->isEmpty($fieldName);
		if (isset($this->vcomponent->$davName)) {
			if ($empty) {
				$this->vcomponent->remove($davName);
			} else {
				$this->vcomponent->$davName = $this->record->get($fieldName);
			}
		} elseif (!$empty) {
			$this->vcomponent->add($this->vcalendar->createProperty($davName, $this->record->get($fieldName)));
		}
	}

	/**
	 * Create status value for dav.
	 */
	private function createStatus()
	{
		$status = $this->record->get('activitystatus');
		if ((string) $this->vcomponent->name === 'VEVENT') {
			$values = [
				'PLL_PLANNED' => 'TENTATIVE',
				'PLL_OVERDUE' => 'CANCELLED',
				'PLL_POSTPONED' => 'CANCELLED',
				'PLL_CANCELLED' => 'CANCELLED',
				'PLL_COMPLETED' => 'CONFIRMED',
			];
		} else {
			$values = [
				'PLL_PLANNED' => 'NEEDS-ACTION',
				'PLL_IN_REALIZATION' => 'IN-PROCESS',
				'PLL_OVERDUE' => 'CANCELLED',
				'PLL_POSTPONED' => 'CANCELLED',
				'PLL_CANCELLED' => 'CANCELLED',
				'PLL_COMPLETED' => 'COMPLETED',
			];
		}
		if ($status && isset($values[$status])) {
			$value = $values[$status];
		} else {
			$value = reset($values);
		}
		if (isset($this->vcomponent->STATUS)) {
			$this->vcomponent->STATUS = $value;
		} else {
			$this->vcomponent->add($this->vcalendar->createProperty('STATUS', $value));
		}
	}

	/**
	 * Create visibility value for dav.
	 */
	private function createVisibility()
	{
		$visibility = $this->record->get('visibility');
		$values = [
			'Public' => 'PUBLIC',
			'Private' => 'PRIVATE'
		];
		$value = 'Private';
		if ($visibility && isset($values[$visibility])) {
			$value = $values[$visibility];
		}
		if (\App\Config::component('Dav', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV') !== false) {
			$value = \App\Config::component('Dav', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV');
		}
		if (isset($this->vcomponent->CLASS)) {
			$this->vcomponent->CLASS = $value;
		} else {
			$this->vcomponent->add($this->vcalendar->createProperty('CLASS', $value));
		}
	}

	/**
	 * Create visibility value for dav.
	 */
	private function createState()
	{
		$state = $this->record->get('state');
		$values = [
			'PLL_OPAQUE' => 'OPAQUE',
			'PLL_TRANSPARENT' => 'TRANSPARENT'
		];
		if ($state && isset($values[$state])) {
			$value = $values[$state];
			if (isset($this->vcomponent->TRANSP)) {
				$this->vcomponent->TRANSP = $value;
			} else {
				$this->vcomponent->add($this->vcalendar->createProperty('TRANSP', $value));
			}
		} elseif (isset($this->vcomponent->TRANSP)) {
			$this->vcomponent->remove('TRANSP');
		}
	}

	/**
	 * Create priority value for dav.
	 */
	private function createPriority()
	{
		$priority = $this->record->get('taskpriority');
		$values = [
			'High' => 1,
			'Medium' => 5,
			'Low' => 9
		];
		$value = 'Medium';
		if ($priority && isset($values[$priority])) {
			$value = $values[$priority];
		}
		if (isset($this->vcomponent->PRIORITY)) {
			$this->vcomponent->PRIORITY = $value;
		} else {
			$this->vcomponent->add($this->vcalendar->createProperty('PRIORITY', $value));
		}
	}

	/**
	 * Create date and time values for dav.
	 */
	private function createDateTime()
	{
		$end = $this->record->get('due_date') . ' ' . $this->record->get('time_end');
		$endField = (string) $this->vcomponent->name == 'VEVENT' ? 'DTEND' : 'DUE';
		$start = new \DateTime($this->record->get('date_start') . ' ' . $this->record->get('time_start'));
		$startProperty = $this->vcalendar->createProperty('DTSTART', $start);
		if ($this->record->get('allday')) {
			$end = new \DateTime($end);
			$end->modify('+1 day');
			$endProperty = $this->vcalendar->createProperty($endField, $end);
			$endProperty['VALUE'] = 'DATE';
			$startProperty['VALUE'] = 'DATE';
		} else {
			$end = new \DateTime($end);
			$endProperty = $this->vcalendar->createProperty($endField, $end);
			if (!$this->createdTimeZone) {
				unset($this->vcalendar->VTIMEZONE);
				$this->vcalendar->add($this->createTimeZone(date_default_timezone_get(), $start->getTimestamp(), $end->getTimestamp()));
				$this->createdTimeZone = true;
			}
		}
		$this->vcomponent->DTSTART = $startProperty;
		$this->vcomponent->$endField = $endProperty;
	}

	/**
	 * Create time zone.
	 *
	 * @param string $tzid
	 * @param int    $from
	 * @param int    $to
	 *
	 * @throws \Exception
	 *
	 * @return \Sabre\VObject\Component
	 */
	public function createTimeZone($tzid, $from = 0, $to = 0)
	{
		if (!$from) {
			$from = time();
		}
		if (!$to) {
			$to = $from;
		}
		try {
			$tz = new \DateTimeZone($tzid);
		} catch (\Exception $e) {
			return false;
		}
		// get all transitions for one year back/ahead
		$year = 86400 * 360;
		$transitions = $tz->getTransitions($from - $year, $to + $year);
		$vt = $this->vcalendar->createComponent('VTIMEZONE');
		$vt->TZID = $tz->getName();
		$vt->TZURL = 'http://tzurl.org/zoneinfo/' . $tz->getName();
		$vt->add('X-LIC-LOCATION', $tz->getName());
		$dst = $std = null;
		foreach ($transitions as $i => $trans) {
			$cmp = null;
			// skip the first entry...
			if ($i == 0) { // ... but remember the offset for the next TZOFFSETFROM value
				$tzfrom = $trans['offset'] / 3600;
				continue;
			}
			// daylight saving time definition
			if ($trans['isdst']) {
				$t_dst = $trans['ts'];
				$dst = $this->vcalendar->createComponent('DAYLIGHT');
				$cmp = $dst;
				$cmpName = 'DAYLIGHT';
			} else { // standard time definition
				$t_std = $trans['ts'];
				$std = $this->vcalendar->createComponent('STANDARD');
				$cmp = $std;
				$cmpName = 'STANDARD';
			}
			if ($cmp && empty($vt->select($cmpName))) {
				$dt = new \DateTime($trans['time']);
				$offset = $trans['offset'] / 3600;
				$cmp->DTSTART = $dt->format('Ymd\THis');
				$cmp->TZOFFSETFROM = sprintf('%s%02d%02d', $tzfrom >= 0 ? '+' : '', floor($tzfrom), ($tzfrom - floor($tzfrom)) * 60);
				$cmp->TZOFFSETTO = sprintf('%s%02d%02d', $offset >= 0 ? '+' : '', floor($offset), ($offset - floor($offset)) * 60);
				// add abbreviated timezone name if available
				if (!empty($trans['abbr'])) {
					$cmp->TZNAME = $trans['abbr'];
				}
				$tzfrom = $offset;
				$vt->add($cmp);
			}
			// we covered the entire date range
			if ($std && $dst && min($t_std, $t_dst) < $from && max($t_std, $t_dst) > $to) {
				break;
			}
		}
		// add X-MICROSOFT-CDO-TZID if available
		$microsoftExchangeMap = array_flip(VObject\TimeZoneUtil::$microsoftExchangeMap);
		if (array_key_exists($tz->getName(), $microsoftExchangeMap)) {
			$vt->add('X-MICROSOFT-CDO-TZID', $microsoftExchangeMap[$tz->getName()]);
		}
		return $vt;
	}
}
