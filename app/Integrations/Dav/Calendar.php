<?php
/**
 * CalDav calendar file.
 *
 * @package Integration
 *
 * @see   https://tools.ietf.org/html/rfc5545
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav;

use Sabre\VObject;

/**
 *  CalDav calendar class.
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
	 * Custom values.
	 *
	 * @var string[]
	 */
	protected static $customValues = [
		'X-MICROSOFT-SKYPETEAMSMEETINGURL' => 'meeting_url',
	];
	/**
	 * Max date.
	 *
	 * @var string
	 */
	const MAX_DATE = '2038-01-01';

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
		$dataReader = (new \App\Db\Query())->select(['calendarid'])->from('dav_calendarobjects')->where(['crmid' => $id])->createCommand()->query();
		$dbCommand->delete('dav_calendarobjects', ['crmid' => $id])->execute();
		while ($calendarId = $dataReader->readColumn(0)) {
			static::addChange($calendarId, $id . '.vcf', 3);
		}
		$dataReader->close();
	}

	/**
	 * Dav delete.
	 *
	 * @param array $calendar
	 *
	 * @throws \yii\db\Exception
	 */
	public static function delete(array $calendar)
	{
		static::addChange($calendar['calendarid'], $calendar['uri'], 3);
		\App\Db::getInstance()->createCommand()->delete('dav_calendarobjects', ['id' => $calendar['id']])->execute();
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
			'operation' => $operation,
		])->execute();
		$dbCommand->update('dav_calendars', [
			'synctoken' => ((int) $calendar['synctoken']) + 1,
		], ['id' => $calendarId])->execute();
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
	 * @param ?string                   $uid
	 *
	 * @return \App\Integrations\Dav\Calendar
	 */
	public static function loadFromContent(string $content, ?\Vtiger_Record_Model $recordModel = null, ?string $uid = null)
	{
		$instance = new self();
		$instance->vcalendar = VObject\Reader::read($content, \Sabre\VObject\Reader::OPTION_FORGIVING);
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
		foreach ($this->vcalendar->getBaseComponents() ?: $this->vcalendar->getComponents() as $component) {
			$type = (string) $component->name;
			if ('VTODO' === $type || 'VEVENT' === $type) {
				$this->vcomponent = $component;
				$this->parseComponent();
			}
		}
		return $this->records;
	}

	/**
	 * Parse component.
	 *
	 * @return void
	 */
	private function parseComponent(): void
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
		$this->parseCustomValues();
	}

	/**
	 * Parse simple text.
	 *
	 * @param string               $fieldName
	 * @param string               $davName
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	private function parseText(string $fieldName, string $davName): void
	{
		$separator = '-::~:~::~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~:~::~:~::-';
		$value = (string) $this->vcomponent->{$davName};
		if (false !== strpos($value, $separator)) {
			[$html,$text] = explode($separator, $value, 2);
			$value = trim(strip_tags($html)) . "\n" . \trim(\str_replace($separator, '', $text));
		} else {
			$value = trim(\str_replace('\n', PHP_EOL, $value));
		}
		$value = \App\Purifier::decodeHtml(\App\Purifier::purify($value));
		if ($length = $this->record->getField($fieldName)->getMaxValue()) {
			$value = \App\TextUtils::textTruncate($value, $length, false);
		}
		$this->record->set($fieldName, \trim($value));
	}

	/**
	 * Parse status.
	 *
	 * @return void
	 */
	private function parseStatus(): void
	{
		$davValue = null;
		if (isset($this->vcomponent->STATUS)) {
			$davValue = strtoupper($this->vcomponent->STATUS->getValue());
		}
		if ('VEVENT' === (string) $this->vcomponent->name) {
			$values = [
				'TENTATIVE' => 'PLL_PLANNED',
				'CANCELLED' => 'PLL_CANCELLED',
				'CONFIRMED' => 'PLL_PLANNED',
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
	 *
	 * @return void
	 */
	private function parseVisibility(): void
	{
		$davValue = null;
		$value = 'Private';
		if (isset($this->vcomponent->CLASS)) {
			$davValue = strtoupper($this->vcomponent->CLASS->getValue());
			$values = [
				'PUBLIC' => 'Public',
				'PRIVATE' => 'Private',
			];
			if ($davValue && isset($values[$davValue])) {
				$value = $values[$davValue];
			}
		}
		$this->record->set('visibility', $value);
	}

	/**
	 * Parse state.
	 *
	 * @return void
	 */
	private function parseState(): void
	{
		$davValue = null;
		$value = '';
		if (isset($this->vcomponent->TRANSP)) {
			$davValue = strtoupper($this->vcomponent->TRANSP->getValue());
			$values = [
				'OPAQUE' => 'PLL_OPAQUE',
				'TRANSPARENT' => 'PLL_TRANSPARENT',
			];
			if ($davValue && isset($values[$davValue])) {
				$value = $values[$davValue];
			}
		}
		$this->record->set('state', $value);
	}

	/**
	 * Parse priority.
	 *
	 * @return void
	 */
	private function parsePriority(): void
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
	 *
	 * @return void
	 */
	private function parseType(): void
	{
		if ($this->record->isEmpty('activitytype')) {
			$this->record->set('activitytype', 'VTODO' === (string) $this->vcomponent->name ? 'Task' : 'Meeting');
		}
	}

	/**
	 * Parse date time.
	 *
	 * @return void
	 */
	private function parseDateTime(): void
	{
		$allDay = 0;
		$endHasTime = $startHasTime = false;
		$endField = 'VEVENT' === ((string) $this->vcomponent->name) ? 'DTEND' : 'DUE';
		if (isset($this->vcomponent->DTSTART)) {
			$timeStamp = $this->vcomponent->DTSTART->getDateTime()->getTimeStamp();
			$dateStart = date('Y-m-d', $timeStamp);
			$timeStart = date('H:i:s', $timeStamp);
			$startHasTime = $this->vcomponent->DTSTART->hasTime();
		} else {
			$timeStamp = $this->vcomponent->DTSTAMP->getDateTime()->getTimeStamp();
			$dateStart = date('Y-m-d', $timeStamp);
			$timeStart = date('H:i:s', $timeStamp);
		}
		if (isset($this->vcomponent->{$endField})) {
			$timeStamp = $this->vcomponent->{$endField}->getDateTime()->getTimeStamp();
			$endHasTime = $this->vcomponent->{$endField}->hasTime();
			$dueDate = date('Y-m-d', $timeStamp);
			$timeEnd = date('H:i:s', $timeStamp);
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
		if (!$startHasTime && !$endHasTime && \App\User::getCurrentUserId()) {
			$allDay = 1;
			$currentUser = \App\User::getCurrentUserModel();
			$userTimeZone = new \DateTimeZone($currentUser->getDetail('time_zone'));
			$sysTimeZone = new \DateTimeZone(\App\Fields\DateTime::getTimeZone());
			[$hour , $minute] = explode(':', $currentUser->getDetail('start_hour'));
			$date = new \DateTime('now', $userTimeZone);
			$date->setTime($hour, $minute);
			$date->setTimezone($sysTimeZone);
			$timeStart = $date->format('H:i:s');

			$date->setTimezone($userTimeZone);
			[$hour , $minute] = explode(':', $currentUser->getDetail('end_hour'));
			$date->setTime($hour, $minute);
			$date->setTimezone($sysTimeZone);
			$timeEnd = $date->format('H:i:s');
		}
		$this->record->set('allday', $allDay);
		$this->record->set('date_start', $dateStart);
		$this->record->set('due_date', $dueDate);
		$this->record->set('time_start', $timeStart);
		$this->record->set('time_end', $timeEnd);
	}

	/**
	 * Parse parse custom values.
	 *
	 * @return void
	 */
	private function parseCustomValues(): void
	{
		foreach (self::$customValues as $key => $fieldName) {
			if (isset($this->vcomponent->{$key})) {
				$this->record->set($fieldName, (string) $this->vcomponent->{$key});
			}
		}
	}

	/**
	 * Create calendar entry component.
	 *
	 * @return \Sabre\VObject\Component
	 */
	public function createComponent()
	{
		$componentType = 'Task' === $this->record->get('activitytype') ? 'VTODO' : 'VEVENT';
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
		if (isset($this->vcomponent->{$davName})) {
			if ($empty) {
				$this->vcomponent->remove($davName);
			} else {
				$this->vcomponent->{$davName} = $this->record->get($fieldName);
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
		if ('VEVENT' === (string) $this->vcomponent->name) {
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
			'Private' => 'PRIVATE',
		];
		$value = 'Private';
		if ($visibility && isset($values[$visibility])) {
			$value = $values[$visibility];
		}
		if (false !== \App\Config::component('Dav', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV')) {
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
			'PLL_TRANSPARENT' => 'TRANSPARENT',
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
			'Low' => 9,
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
		$endField = 'VEVENT' == (string) $this->vcomponent->name ? 'DTEND' : 'DUE';
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
		$this->vcomponent->{$endField} = $endProperty;
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
			if (0 == $i) { // ... but remember the offset for the next TZOFFSETFROM value
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
		if (\array_key_exists($tz->getName(), $microsoftExchangeMap)) {
			$vt->add('X-MICROSOFT-CDO-TZID', $microsoftExchangeMap[$tz->getName()]);
		}
		return $vt;
	}

	/**
	 * Get invitations for record id.
	 *
	 * @param int $recordId
	 *
	 * @return array
	 */
	public function getInvitations(int $recordId): array
	{
		$invities = [];
		$dataReader = (new \App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => $recordId])->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (!empty($row['email'])) {
				$invities[$row['email']] = $row;
			}
		}
		return $invities;
	}

	/**
	 * Record save attendee.
	 *
	 * @param Vtiger_Record_Model $record
	 */
	public function recordSaveAttendee(\Vtiger_Record_Model $record)
	{
		if ('VEVENT' === (string) $this->vcomponent->name) {
			$invities = $this->getInvitations($record->getId());
			$time = VObject\DateTimeParser::parse($this->vcomponent->DTSTAMP);
			$timeFormated = $time->format('Y-m-d H:i:s');
			$dbCommand = \App\Db::getInstance()->createCommand();
			$attendees = $this->vcomponent->select('ATTENDEE');
			foreach ($attendees as &$attendee) {
				$nameAttendee = isset($attendee->parameters['CN']) ? $attendee->parameters['CN']->getValue() : null;
				$value = $attendee->getValue();
				if (0 === stripos($value, 'mailto:')) {
					$value = substr($value, 7, \strlen($value) - 7);
				}
				if ($value && \App\TextUtils::getTextLength($value) > 100 || !\App\Validator::email($value)) {
					throw new \Sabre\DAV\Exception\BadRequest('Invalid email: ' . $value);
				}
				if (isset($attendee['ROLE']) && 'CHAIR' === $attendee['ROLE']->getValue()) {
					$users = $this->findRecordByEmail($value, ['Users']);
					if (!empty($users)) {
						continue;
					}
				}
				$crmid = 0;
				$records = $this->findRecordByEmail($value, array_keys(array_merge(\App\ModuleHierarchy::getModulesByLevel(0), \App\ModuleHierarchy::getModulesByLevel(4))));
				if (!empty($records)) {
					$recordCrm = current($records);
					$crmid = $recordCrm['crmid'];
				}
				$status = $this->getAttendeeStatus(isset($attendee['PARTSTAT']) ? $attendee['PARTSTAT']->getValue() : '');
				if (isset($invities[$value])) {
					$row = $invities[$value];
					if ($row['status'] !== $status || $row['name'] !== $nameAttendee) {
						$dbCommand->update('u_#__activity_invitation', [
							'status' => $status,
							'time' => $timeFormated,
							'name' => \App\TextUtils::textTruncate($nameAttendee, 500, false),
						], ['activityid' => $record->getId(), 'email' => $value]
					)->execute();
					}
					unset($invities[$value]);
				} else {
					$params = [
						'email' => $value,
						'crmid' => $crmid,
						'status' => $status,
						'name' => \App\TextUtils::textTruncate($nameAttendee, 500, false),
						'activityid' => $record->getId(),
					];
					if ($status) {
						$params['time'] = $timeFormated;
					}
					$dbCommand->insert('u_#__activity_invitation', $params)->execute();
				}
			}
			foreach ($invities as &$invitation) {
				$dbCommand->delete('u_#__activity_invitation', ['inviteesid' => $invitation['inviteesid']])->execute();
			}
		}
	}

	/**
	 * Dav save attendee.
	 *
	 * @param array $record
	 */
	public function davSaveAttendee(array $record)
	{
		$owner = \Users_Privileges_Model::getInstanceById($record['assigned_user_id']);
		$invities = $this->getInvitations($record['id']);
		$attendees = $this->vcomponent->select('ATTENDEE');
		if (empty($attendees)) {
			if (!empty($invities)) {
				$organizer = $this->vcalendar->createProperty('ORGANIZER', 'mailto:' . $owner->get('email1'));
				$organizer->add('CN', $owner->getName());
				$this->vcomponent->add($organizer);
				$attendee = $this->vcalendar->createProperty('ATTENDEE', 'mailto:' . $owner->get('email1'));
				$attendee->add('CN', $owner->getName());
				$attendee->add('ROLE', 'CHAIR');
				$attendee->add('PARTSTAT', 'ACCEPTED');
				$attendee->add('RSVP', 'false');
				$this->vcomponent->add($attendee);
			}
		} else {
			foreach ($attendees as &$attendee) {
				$value = ltrim($attendee->getValue(), 'mailto:');
				if (isset($invities[$value])) {
					$row = $invities[$value];
					if (isset($attendee['PARTSTAT'])) {
						$attendee['PARTSTAT']->setValue($this->getAttendeeStatus($row['status'], false));
					} else {
						$attendee->add('PARTSTAT', $this->getAttendeeStatus($row['status']));
					}
					unset($invities[$value]);
				} else {
					$this->vcomponent->remove($attendee);
				}
			}
		}
		foreach ($invities as &$row) {
			$attendee = $this->vcalendar->createProperty('ATTENDEE', 'mailto:' . $row['email']);
			$attendee->add('CN', empty($row['crmid']) ? $row['name'] : \App\Record::getLabel($row['crmid']));
			$attendee->add('ROLE', 'REQ-PARTICIPANT');
			$attendee->add('PARTSTAT', $this->getAttendeeStatus($row['status'], false));
			$attendee->add('RSVP', '0' == $row['status'] ? 'true' : 'false');
			$this->vcomponent->add($attendee);
		}
	}

	/**
	 * Get attendee status.
	 *
	 * @param string $value
	 * @param bool   $toCrm
	 *
	 * @return false|string
	 */
	public function getAttendeeStatus(string $value, bool $toCrm = true)
	{
		$statuses = ['NEEDS-ACTION', 'ACCEPTED', 'DECLINED'];
		if ($toCrm) {
			$status = false;
			$statuses = array_flip($statuses);
		} else {
			$status = 'NEEDS-ACTION';
		}
		if (isset($statuses[$value])) {
			$status = $statuses[$value];
		}
		return $status;
	}

	/**
	 * Parses some information from calendar objects, used for optimized
	 * calendar-queries.
	 *
	 * Returns an array with the following keys:
	 *   * etag - An md5 checksum of the object without the quotes.
	 *   * size - Size of the object in bytes
	 *   * componentType - VEVENT, VTODO or VJOURNAL
	 *   * firstOccurence
	 *   * lastOccurence
	 *   * uid - value of the UID property
	 *
	 * @param string $calendarData
	 *
	 * @return array
	 */
	public function getDenormalizedData($calendarData)
	{
		$vObject = VObject\Reader::read($calendarData);
		$uid = $lastOccurence = $firstOccurence = $component = $componentType = null;
		foreach ($vObject->getComponents() as $component) {
			if ('VTIMEZONE' !== $component->name) {
				$componentType = $component->name;
				$uid = (string) $component->UID;
				break;
			}
		}
		if (!$componentType) {
			throw new \Sabre\DAV\Exception\BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
		}
		if ('VEVENT' === $componentType) {
			$firstOccurence = $component->DTSTART->getDateTime()->getTimeStamp();
			// Finding the last occurence is a bit harder
			if (!isset($component->RRULE)) {
				if (isset($component->DTEND)) {
					$lastOccurence = $component->DTEND->getDateTime()->getTimeStamp();
				} elseif (isset($component->DURATION)) {
					$endDate = clone $component->DTSTART->getDateTime();
					$endDate = $endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
					$lastOccurence = $endDate->getTimeStamp();
				} elseif (!$component->DTSTART->hasTime()) {
					$endDate = clone $component->DTSTART->getDateTime();
					$endDate = $endDate->modify('+1 day');
					$lastOccurence = $endDate->getTimeStamp();
				} else {
					$lastOccurence = $firstOccurence;
				}
			} else {
				$it = new VObject\Recur\EventIterator($vObject, (string) $component->UID);
				$maxDate = new \DateTime(self::MAX_DATE);
				if ($it->isInfinite()) {
					$lastOccurence = $maxDate->getTimeStamp();
				} else {
					$end = $it->getDtEnd();
					while ($it->valid() && $end < $maxDate) {
						$end = $it->getDtEnd();
						$it->next();
					}
					$lastOccurence = $end->getTimeStamp();
				}
			}
			// Ensure Occurence values are positive
			if ($firstOccurence < 0) {
				$firstOccurence = 0;
			}
			if ($lastOccurence < 0) {
				$lastOccurence = 0;
			}
		}
		// Destroy circular references to PHP will GC the object.
		$vObject->destroy();
		return [
			'etag' => md5($calendarData),
			'size' => \strlen($calendarData),
			'componentType' => $componentType,
			'firstOccurence' => $firstOccurence,
			'lastOccurence' => $lastOccurence,
			'uid' => $uid,
		];
	}

	/**
	 * Find crm id by email.
	 *
	 * @param int|string $value
	 * @param array      $allowedModules
	 * @param array      $skipModules
	 *
	 * @return array
	 */
	public function findRecordByEmail($value, array $allowedModules = [], array $skipModules = [])
	{
		$db = \App\Db::getInstance();
		$rows = $fields = [];
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.columnname', 'vtiger_field.tablename', 'vtiger_field.fieldlabel', 'vtiger_field.tabid', 'vtiger_tab.name'])
			->from('vtiger_field')->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
			->where(['vtiger_tab.presence' => 0])
			->andWhere(['<>', 'vtiger_field.presence', 1])
			->andWhere(['or', ['uitype' => 13], ['uitype' => 104]])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$fields[$row['name']][$row['tablename']][$row['columnname']] = $row;
		}
		$queryUnion = null;
		foreach ($fields as $moduleName => $moduleFields) {
			if (($allowedModules && !\in_array($moduleName, $allowedModules)) || \in_array($moduleName, $skipModules)) {
				continue;
			}
			$instance = \CRMEntity::getInstance($moduleName);
			$isEntityType = isset($instance->tab_name_index['vtiger_crmentity']);
			foreach ($moduleFields as $tablename => $columns) {
				$tableIndex = $instance->tab_name_index[$tablename];
				$query = (new \App\Db\Query())->select(['crmid' => $tableIndex, 'modules' => new \yii\db\Expression($db->quoteValue($moduleName))])
					->from($tablename);
				if ($isEntityType) {
					$query->innerJoin('vtiger_crmentity', "vtiger_crmentity.crmid = {$tablename}.{$tableIndex}")->where(['vtiger_crmentity.deleted' => 0]);
				}
				$orWhere = ['or'];
				foreach ($columns as $row) {
					$orWhere[] = ["{$row['tablename']}.{$row['columnname']}" => $value];
				}
				$query->andWhere($orWhere);
				if ($queryUnion) {
					$queryUnion->union($query);
				} else {
					$queryUnion = $query;
				}
			}
		}
		$rows = $queryUnion->all();
		$labels = \App\Record::getLabel(array_column($rows, 'crmid'));
		foreach ($rows as &$row) {
			$row['label'] = $labels[$row['crmid']];
		}
		return $rows;
	}
}
