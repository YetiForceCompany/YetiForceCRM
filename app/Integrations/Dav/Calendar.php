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

	/**
	 * Create instance from dav data.
	 *
	 * @param string $calendar
	 *
	 * @return \App\Integrations\Dav\Calendar
	 */
	public static function loadFromDav(string $calendar)
	{
		$instance = new self();
		$instance->record = \Vtiger_Record_Model::getCleanInstance('Calendar');
		$instance->vcalendar = VObject\Reader::read($calendar);
		$instance->vcomponent = current($instance->vcalendar->getBaseComponents());
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
	 * @param string                    $content
	 * @param \Vtiger_Record_Model|null $recordModel
	 *
	 * @return \App\Integrations\Dav\Calendar
	 */
	public static function loadFromContent(string $content, ?\Vtiger_Record_Model $recordModel = null, ?string $uid = null)
	{
		$instance = new self();
		$instance->vcalendar = VObject\Reader::read($content);
		if ($recordModel && $uid) {
			$instance->records[$uid] = $recordModel;
		}
		return $instance;
	}

	/**
	 * Get VCalendar instance.
	 *
	 * @return \Sabre\VObject\Component\VCalendar
	 */
	public function getVCalendar()
	{
		return $this->vcalendar;
	}

	/**
	 * Get calendar component instance.
	 *
	 * @return \Sabre\VObject\Component
	 */
	public function getComponent()
	{
		return $this->vcomponent;
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
				$this->vcomponent = $component;
				$this->parseComponent();
			}
		}
		return $this->records;
	}

	/**
	 * Parse component.
	 */
	private function parseComponent()
	{
		$uid = (string) $this->vcomponent->UID;
		if (isset($this->records[$uid])) {
			$this->record = $this->records[$uid];
		} else {
			$this->record = $this->records[$uid] = \Vtiger_Record_Model::getCleanInstance('Calendar');
		}
		$this->parseText('subject', 'SUMMARY');
		$this->parseText('location', 'LOCATION');
		$this->parseText('description', 'DESCRIPTION');
		$this->parseStatus();
		$this->parsePriority();
		$this->parseVisibility();
		$this->parseState();
		$this->parseType();
		$this->parseDateTime();
	}

	/**
	 * Parse simple text.
	 *
	 * @param string               $fieldName
	 * @param string               $davName
	 * @param \Vtiger_Record_Model $recordModel
	 */
	private function parseText(string $fieldName, string $davName)
	{
		$value = \str_replace([
			'-::~:~::~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~::~:~::-'
		], '', \App\Purifier::purify((string) $this->vcomponent->$davName));
		if ($length = $this->record->getField($fieldName)->get('maximumlength')) {
			$value = \App\TextParser::textTruncate($value, $length, false);
		}
		$this->record->set($fieldName, \trim($value));
	}

	/**
	 * Parse status.
	 */
	private function parseStatus()
	{
		$davValue = null;
		if (isset($this->vcomponent->STATUS)) {
			$davValue = strtoupper($this->vcomponent->STATUS->getValue());
		}
		if ((string) $this->vcomponent->name === 'VEVENT') {
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
		$this->record->set('activitystatus', $value);
	}

	/**
	 * Parse visibility.
	 */
	private function parseVisibility()
	{
		$davValue = null;
		$value = 'Private';
		if (isset($this->vcomponent->CLASS)) {
			$davValue = strtoupper($this->vcomponent->CLASS->getValue());
			$values = [
				'PUBLIC' => 'Public',
				'PRIVATE' => 'Private'
			];
			if ($davValue && isset($values[$davValue])) {
				$value = $values[$davValue];
			}
		}
		$this->record->set('visibility', $value);
	}

	/**
	 * Parse state.
	 */
	private function parseState()
	{
		$davValue = null;
		$value = '';
		if (isset($this->vcomponent->TRANSP)) {
			$davValue = strtoupper($this->vcomponent->TRANSP->getValue());
			$values = [
				'OPAQUE' => 'PLL_OPAQUE',
				'TRANSPARENT' => 'PLL_TRANSPARENT'
			];
			if ($davValue && isset($values[$davValue])) {
				$value = $values[$davValue];
			}
		}
		$this->record->set('state', $value);
	}

	/**
	 * Parse priority.
	 */
	private function parsePriority()
	{
		$davValue = null;
		$value = 'Medium';
		if (isset($this->vcomponent->PRIORITY)) {
			$davValue = strtoupper($this->vcomponent->PRIORITY->getValue());
			$values = [
				1 => 'High',
				5 => 'Medium',
				9 => 'Low',
			];
			if ($davValue && isset($values[$davValue])) {
				$value = $values[$davValue];
			}
		}
		$this->record->set('taskpriority', $value);
	}

	/**
	 * Parse type.
	 */
	private function parseType()
	{
		if ($this->record->isEmpty('activitytype')) {
			$this->record->set('activitytype', (string) $this->vcomponent->name === 'VTODO' ? 'Task' : 'Meeting');
		}
	}

	/**
	 * Parse date time.
	 */
	private function parseDateTime()
	{
		$allDay = 0;
		$endHasTime = $startHasTime = false;
		$endField = ((string) $this->vcomponent->name) === 'VEVENT' ? 'DTEND' : 'DUE';
		if (isset($this->vcomponent->DTSTART)) {
			$davStart = VObject\DateTimeParser::parse($this->vcomponent->DTSTART);
			$dateStart = $davStart->format('Y-m-d');
			$timeStart = $davStart->format('H:i:s');
			$startHasTime = $this->vcomponent->DTSTART->hasTime();
		} else {
			$davStart = VObject\DateTimeParser::parse($this->vcomponent->DTSTAMP);
			$dateStart = $davStart->format('Y-m-d');
			$timeStart = $davStart->format('H:i:s');
		}
		if (isset($this->vcomponent->$endField)) {
			$davEnd = VObject\DateTimeParser::parse($this->vcomponent->$endField);
			$endHasTime = $this->vcomponent->$endField->hasTime();
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
		$this->record->set('allday', $allDay);
		$this->record->set('date_start', $dateStart);
		$this->record->set('due_date', $dueDate);
		$this->record->set('time_start', $timeStart);
		$this->record->set('time_end', $timeEnd);
	}

	/**
	 * Create calendar entry component.
	 *
	 * @return \Sabre\VObject\Component
	 */
	public function createComponent()
	{
		$componentType = $this->record->get('activitytype') === 'Task' ? 'VTODO' : 'VEVENT';
		$this->vcomponent = $this->vcalendar->createComponent($componentType);
		$this->vcomponent->UID = \str_replace('sabre-vobject', 'YetiForceCRM', (string) $this->vcomponent->UID);
		$this->updateComponent();
		$this->vcalendar->add($this->vcomponent);
		return $this->vcomponent;
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
		if (empty($this->vcomponent->SEQUENCE)) {
			$this->vcomponent->add($this->vcalendar->createProperty('SEQUENCE', 1));
		} else {
			$this->vcomponent->SEQUENCE = $this->vcomponent->SEQUENCE->getValue() + 1;
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
		$value = 5;
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
				$offset = $trans['offset'] / 3600;
				$cmp->TZOFFSETFROM = sprintf('%s%02d%02d', $tzfrom >= 0 ? '+' : '', floor($tzfrom), ($tzfrom - floor($tzfrom)) * 60);
				$cmp->TZOFFSETTO = sprintf('%s%02d%02d', $offset >= 0 ? '+' : '', floor($offset), ($offset - floor($offset)) * 60);
				// add abbreviated timezone name if available
				if (!empty($trans['abbr'])) {
					$cmp->TZNAME = $trans['abbr'];
				}
				$dt = new \DateTime($trans['time']);
				$cmp->DTSTART = $dt->format('Ymd\THis');
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
