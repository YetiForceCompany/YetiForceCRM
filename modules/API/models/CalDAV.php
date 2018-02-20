<?php

/**
 * Api CalDAV Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_CalDAV_Model
{
	/**
	 * Prod id.
	 *
	 * @var string
	 */
	const PRODID = 'YetiForce';

	/**
	 * Calendar name.
	 *
	 * @var string
	 */
	const CALENDAR_NAME = 'YFCalendar';

	/**
	 * Components.
	 *
	 * @var string
	 */
	const COMPONENTS = 'VEVENT,VTODO';

	/**
	 * User.
	 *
	 * @var mixed|bool
	 */
	public $user = false;

	/**
	 * Record.
	 *
	 * @var array
	 */
	public $record = false;

	/**
	 * Calendar id.
	 *
	 * @var int
	 */
	public $calendarId = false;

	/**
	 * Dav users.
	 *
	 * @var array
	 */
	public $davUsers = [];

	/**
	 * Crm records.
	 *
	 * @var array
	 */
	protected $crmRecords = [];

	/**
	 * Max date.
	 *
	 * @var string
	 */
	const MAX_DATE = '2038-01-01';

	/**
	 * calDavCrm2Dav.
	 */
	public function calDavCrm2Dav()
	{
		\App\Log::trace(__METHOD__ . ' | Start');

		$query = (new \App\Db\Query())->select(['vtiger_activity.*', 'vtiger_crmentity.crmid', 'vtiger_crmentity.smownerid', 'vtiger_crmentity.deleted', 'vtiger_crmentity.createdtime', 'vtiger_crmentity.modifiedtime', 'vtiger_crmentity.description'])->from('vtiger_activity')->innerJoin('vtiger_crmentity', 'vtiger_activity.activityid = vtiger_crmentity.crmid')->where(['vtiger_crmentity.deleted' => 0, 'vtiger_activity.dav_status' => 1]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$this->record = $row;
			$this->davSync();
		}
		$dataReader->close();
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Dav sync.
	 */
	public function davSync()
	{
		foreach ($this->davUsers as &$user) {
			$this->calendarId = $user->get('calendarsid');
			$accessibleGroups = \App\Fields\Owner::getInstance(false, $user)->getAccessibleGroups();
			if ($this->record['smownerid'] == $user->get('id') || $this->record['visibility'] == 'Public' || isset($accessibleGroups[$this->record['smownerid']])) {
				$sync = true;
				$exclusion = AppConfig::module('API', 'CALDAV_EXCLUSION_TO_DAV');
				if ($exclusion !== false) {
					foreach ($exclusion as $key => $value) {
						if ($this->record[$key] == $value) {
							$sync = false;
						}
					}
				}
				if ($sync) {
					$orgUserId = App\User::getCurrentUserId();
					App\User::setCurrentUserId($user->get('id'));
					$vcalendar = $this->getDavDetail();
					if ($vcalendar === false) {
						// Creating
						$this->davCreate();
					//} elseif($this->record['deleted'] == 1){
					} elseif (strtotime($this->record['modifiedtime']) > $vcalendar['lastmodified']) { // Updating
						$this->davUpdate($vcalendar);
					}
					App\User::setCurrentUserId($orgUserId);
				}
			}
		}
		$this->recordMarkComplete();
	}

	/**
	 * Dav create.
	 */
	public function davCreate()
	{
		$record = $this->record;
		\App\Log::trace(__METHOD__ . ' | Start CRM ID:' . $record['crmid']);
		$calType = $record['activitytype'] == 'Task' ? 'VTODO' : 'VEVENT';
		$endField = $this->getEndFieldName($calType);
		$uid = date('Y-m-d\THis') . '-' . $record['crmid'];
		$calUri = $uid . '.ics';

		$vcalendar = new Sabre\VObject\Component\VCalendar();
		$vcalendar->PRODID = '-//' . self::PRODID . ' V' . \App\Version::get() . '//';
		$start = $record['date_start'] . ' ' . $record['time_start'];
		$end = $record['due_date'] . ' ' . $record['time_end'];

		$startDT = new \DateTime($start);
		$dtstart = $vcalendar->createProperty('DTSTART', $startDT);
		$createdTime = new \DateTime($record['createdtime']);
		$createdTime->setTimezone(new DateTimeZone('UTC'));
		$created = $vcalendar->createProperty('CREATED', $createdTime);

		if ($record['allday']) {
			$endDT = new DateTime($end);
			$endDT->modify('+1 day');
			$dtend = $vcalendar->createProperty($endField, $endDT);
			$dtend['VALUE'] = 'DATE';
			$dtstart['VALUE'] = 'DATE';
		} else {
			$endDT = new \DateTime($end);
			$dtend = $vcalendar->createProperty($endField, $endDT);
			$dtz = date_default_timezone_get();
			$vTimeZone = self::getVTimeZone($vcalendar, $dtz, $startDT->getTimestamp(), $endDT->getTimestamp());
			$vcalendar->add($vTimeZone);
		}
		$component = $vcalendar->createComponent($calType);
		$component->UID = $uid;
		$component->add($created);
		$component->add($dtstart);
		$component->add($dtend);
		$component->add($vcalendar->createProperty('SUMMARY', $record['subject']));
		if (!empty($record['location'])) {
			$component->add($vcalendar->createProperty('LOCATION', $record['location']));
		}
		if (!empty($record['description'])) {
			$component->add($vcalendar->createProperty('DESCRIPTION', $record['description']));
		}
		if (AppConfig::module('API', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV') !== false) {
			$record['visibility'] = AppConfig::module('API', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV');
		}
		$component->add($vcalendar->createProperty('CLASS', $record['visibility'] == 'Private' ? 'PRIVATE' : 'PUBLIC'));
		$component->add($vcalendar->createProperty('PRIORITY', $this->getPriorityFromCrm($record['priority'])));

		$status = $this->getStatusFromCrm($record['status'], $calType);
		if ($status) {
			$component->add($vcalendar->createProperty('STATUS', $status));
		}
		$state = $this->getStateFromCrm($record['state']);
		if ($state) {
			$component->add($vcalendar->createProperty('TRANSP', $state));
		}
		if ($calType == 'VEVENT') {
			$this->davSaveAttendee($record, $vcalendar, $component);
		}
		$component->SEQUENCE = 0;
		$vcalendar->add($component);
		$calendarData = $vcalendar->serialize();
		$modifiedtime = strtotime($record['modifiedtime']);
		$extraData = $this->getDenormalizedData($calendarData);
		\App\Db::getInstance()->createCommand()->insert('dav_calendarobjects', [
			'calendarid' => $this->calendarId,
			'uri' => $calUri,
			'calendardata' => $calendarData,
			'lastmodified' => $modifiedtime,
			'etag' => $extraData['etag'],
			'size' => $extraData['size'],
			'componenttype' => $extraData['componentType'],
			'firstoccurence' => $extraData['firstOccurence'],
			'lastoccurence' => $extraData['lastOccurence'],
			'uid' => $uid,
			'crmid' => $record['crmid'],
		])->execute();
		$this->addChange($calUri, 1);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Dav update.
	 *
	 * @param array $calendar
	 */
	public function davUpdate($calendar)
	{
		$record = $this->record;
		\App\Log::trace(__METHOD__ . ' | Start CRM ID:' . $record['crmid']);

		$calType = $record['activitytype'] == 'Task' ? 'VTODO' : 'VEVENT';
		$endField = $this->getEndFieldName($calType);

		$vcalendar = Sabre\VObject\Reader::read($calendar['calendardata']);
		$vcalendar->PRODID = '-//' . self::PRODID . ' V' . \App\Version::get() . '//';
		$start = $record['date_start'] . ' ' . $record['time_start'];
		$end = $record['due_date'] . ' ' . $record['time_end'];

		$startDT = new \DateTime($start);
		$dtstart = $vcalendar->createProperty('DTSTART', $startDT);
		if ($record['allday']) {
			$endDT = new DateTime($end);
			$endDT->modify('+1 day');
			$dtend = $vcalendar->createProperty($endField, $endDT);
			$dtend['VALUE'] = 'DATE';
			$dtstart['VALUE'] = 'DATE';
		} else {
			$endDT = new \DateTime($end);
			$dtend = $vcalendar->createProperty($endField, $endDT);
			unset($vcalendar->VTIMEZONE);
			$dtz = date_default_timezone_get();
			$vTimeZone = self::getVTimeZone($vcalendar, $dtz, $startDT->getTimestamp(), $endDT->getTimestamp());
			$vcalendar->add($vTimeZone);
		}
		if (AppConfig::module('API', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV') !== false) {
			$record['visibility'] = AppConfig::module('API', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV');
		}
		foreach ($vcalendar->getBaseComponents() as $component) {
			if ($component->name = $calType) {
				$component->DTSTART = $dtstart;
				$component->$endField = $dtend;
				$component->SUMMARY = $record['subject'];
				$component->LOCATION = $record['location'];
				$component->DESCRIPTION = $record['description'];
				$component->CLASS = $record['visibility'] == 'Private' ? 'PRIVATE' : 'PUBLIC';
				$component->PRIORITY = $this->getPriorityFromCrm($record['priority']);
				$status = $this->getStatusFromCrm($record['status'], $calType);
				if ($status) {
					$component->STATUS = $status;
				}
				$state = $this->getStateFromCrm($record['state']);
				if ($state) {
					$component->TRANSP = $state;
				}
				if (isset($component->SEQUENCE)) {
					$seq = (int) $component->SEQUENCE->getValue();
					++$seq;
					$component->SEQUENCE->setValue($seq);
				} else {
					$component->SEQUENCE = 1;
				}
				if ($calType == 'VEVENT') {
					$this->davSaveAttendee($record, $vcalendar, $component);
				}
			}
		}
		$calendarData = $vcalendar->serialize();
		$modifiedtime = strtotime($record['modifiedtime']);
		$extraData = $this->getDenormalizedData($calendarData);
		\App\Db::getInstance()->createCommand()->update('dav_calendarobjects', [
			'calendardata' => $calendarData,
			'lastmodified' => $modifiedtime,
			'etag' => $extraData['etag'],
			'size' => $extraData['size'],
			'componenttype' => $extraData['componentType'],
			'firstoccurence' => $extraData['firstOccurence'],
			'lastoccurence' => $extraData['lastOccurence'],
			'uid' => $extraData['uid'],
			'crmid' => $record['crmid'],
			], ['id' => $calendar['id']]
		)->execute();
		$this->addChange($calendar['uri'], 2);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Dav delete.
	 *
	 * @param array $calendar
	 */
	public function davDelete($calendar)
	{
		\App\Log::trace(__METHOD__ . ' | Start Calendar ID:' . $card['id']);
		$this->addChange($calendar['uri'], 3);
		\App\Db::getInstance()->createCommand()->delete('dav_calendarobjects', ['id' => $calendar['id']])->execute();
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Cal dav to crm.
	 */
	public function calDav2Crm()
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		foreach ($this->davUsers as $key => $user) {
			$this->calendarId = $user->get('calendarsid');
			$this->user = $user;
			$this->recordSync();
		}
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Sync record.
	 */
	public function recordSync()
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		$query = (new \App\Db\Query())->select(['dav_calendarobjects.*', 'vtiger_crmentity.modifiedtime', 'vtiger_crmentity.setype', 'vtiger_crmentity.smownerid'])->from('dav_calendarobjects')->leftJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = dav_calendarobjects.crmid')->where(['calendarid' => $this->calendarId]);
		$skipped = $create = $deletes = $updates = 0;
		$dataReader = $query->createCommand()->query();

		while ($row = $dataReader->read()) {
			if (!$row['crmid']) { //Creating
				if ($this->recordCreate($row)) {
					$create++;
				}
				++$skipped;
			} elseif ($this->toDelete($row)) { // Deleting
				$this->davDelete($row);
				++$deletes;
			} else {
				if (strtotime($row['modifiedtime']) < $row['lastmodified']) { // Updating
					$recordModel = Vtiger_Record_Model::getInstanceById($row['crmid']);
					if ($this->recordUpdate($recordModel, $row)) {
						$updates++;
					}
					++$skipped;
				}
			}
		}
		$dataReader->close();
		\App\Log::trace("calDav2Crm | create: $create | deletes: $deletes | updates: $updates | skipped: $skipped");
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Record create.
	 *
	 * @param array $cal
	 *
	 * @return bool
	 */
	public function recordCreate($cal)
	{
		\App\Log::trace(__METHOD__ . ' | Start Cal ID' . $cal['id']);

		$vcalendar = Sabre\VObject\Reader::read($cal['calendardata']);
		foreach ($vcalendar->getBaseComponents() as $component) {
			$type = (string) $component->name;
			if ($type === 'VTODO' || $type === 'VEVENT') {
				$dates = $this->getEventDates($component);
				$record = Vtiger_Record_Model::getCleanInstance('Calendar');
				$record->set('assigned_user_id', $this->user->get('id'));
				$record->set('subject', \App\Purifier::purify((string) $component->SUMMARY));
				$record->set('location', \App\Purifier::purify((string) $component->LOCATION));
				$record->set('description', \App\Purifier::purify((string) $component->DESCRIPTION));
				$record->set('allday', $dates['allday']);
				$record->set('date_start', $dates['date_start']);
				$record->set('due_date', $dates['due_date']);
				$record->set('time_start', $dates['time_start']);
				$record->set('time_end', $dates['time_end']);
				$record->set('activitystatus', $this->getStatusFromDav($component, $type));
				if ($type === 'VTODO') {
					$record->set('activitytype', 'Task');
				} else {
					$record->set('activitytype', 'Meeting');
				}
				$record->set('taskpriority', $this->getPriorityFromDav($component));
				$record->set('visibility', $this->getVisibility($component));
				$record->set('state', $this->getStateFromDav($component));

				$exclusion = AppConfig::module('API', 'CALDAV_EXCLUSION_FROM_DAV');
				if ($exclusion !== false) {
					foreach ($exclusion as $key => $value) {
						if ($record->get($key) == $value) {
							\App\Log::info(__METHOD__ . ' | End exclusion');

							return false;
						}
					}
				}
				if (AppConfig::module('API', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV') !== false) {
					$record->set('visibility', AppConfig::module('API', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV'));
				}
				$record->save();

				$dbCommand = \App\Db::getInstance()->createCommand();
				$dbCommand->update('dav_calendarobjects', [
					'crmid' => $record->getId(),
					], ['id' => $cal['id']]
				)->execute();
				$dbCommand->update('vtiger_crmentity', [
					'modifiedtime' => date('Y-m-d H:i:s', $cal['lastmodified']),
					], ['crmid' => $record->getId()]
				)->execute();
				if ($type === 'VEVENT') {
					$this->recordSaveAttendee($record, $component);
				}
			}
		}

		\App\Log::trace(__METHOD__ . ' | End');

		return true;
	}

	/**
	 * Record update.
	 *
	 * @param Vtiger_Record_Model $record
	 * @param array               $cal
	 *
	 * @return bool
	 */
	public function recordUpdate(Vtiger_Record_Model $record, $cal)
	{
		\App\Log::trace(__METHOD__ . ' | Start Cal ID:' . $cal['id']);
		$vcalendar = Sabre\VObject\Reader::read($cal['calendardata']);

		foreach ($vcalendar->getBaseComponents() as $component) {
			$type = (string) $component->name;
			if ($type === 'VTODO' || $type === 'VEVENT') {
				$dates = $this->getEventDates($component);
				$record->set('assigned_user_id', $this->user->get('id'));
				$record->set('subject', \App\Purifier::purify((string) $component->SUMMARY));
				$record->set('location', \App\Purifier::purify((string) $component->LOCATION));
				$record->set('description', \App\Purifier::purify((string) $component->DESCRIPTION));
				$record->set('allday', $dates['allday']);
				$record->set('date_start', $dates['date_start']);
				$record->set('due_date', $dates['due_date']);
				$record->set('time_start', $dates['time_start']);
				$record->set('time_end', $dates['time_end']);
				$record->set('activitystatus', $this->getStatusFromDav($component, $type));
				if ($type === 'VTODO') {
					$record->set('activitytype', 'Task');
				} else {
					$record->set('activitytype', 'Meeting');
				}
				$record->set('taskpriority', $this->getPriorityFromDav($component));
				$record->set('visibility', $this->getVisibility($component));
				$record->set('state', $this->getStateFromDav($component));

				$exclusion = AppConfig::module('API', 'CALDAV_EXCLUSION_FROM_DAV');
				if ($exclusion !== false) {
					foreach ($exclusion as $key => $value) {
						if ($record->get($key) == $value) {
							\App\Log::info(__METHOD__ . ' | End exclusion');

							return false;
						}
					}
				}
				if (AppConfig::module('API', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV') !== false) {
					$record->set('visibility', AppConfig::module('API', 'CALDAV_DEFAULT_VISIBILITY_FROM_DAV'));
				}
				$record->save();
				$dbCommand = \App\Db::getInstance()->createCommand();
				$dbCommand->update('dav_calendarobjects', [
					'crmid' => $record->getId(),
					], ['id' => $cal['id']]
				)->execute();
				$dbCommand->update('vtiger_crmentity', [
					'modifiedtime' => date('Y-m-d H:i:s', $cal['lastmodified']),
					], ['crmid' => $record->getId()]
				)->execute();
				if ($type === 'VEVENT') {
					$this->recordSaveAttendee($record, $component);
				}
			}
		}
		\App\Log::trace(__METHOD__ . ' | End');

		return true;
	}

	/**
	 * Get event dates.
	 *
	 * @param Sabre\VObject\Component $component
	 *
	 * @return array
	 */
	public function getEventDates(Sabre\VObject\Component $component)
	{
		$allday = 0;
		$endField = $this->getEndFieldName($component->name);
		// Start
		if (isset($component->DTSTART)) {
			$DTSTART = Sabre\VObject\DateTimeParser::parse($component->DTSTART);
			$dateStart = $DTSTART->format('Y-m-d');
			$timeStart = $DTSTART->format('H:i:s');
			$startHasTime = $component->DTSTART->hasTime();
		} else {
			$DTSTAMP = Sabre\VObject\DateTimeParser::parse($component->DTSTAMP);
			$dateStart = $DTSTAMP->format('Y-m-d');
			$timeStart = $DTSTAMP->format('H:i:s');
		}
		//End
		if (isset($component->$endField)) {
			$DTEND = Sabre\VObject\DateTimeParser::parse($component->$endField);
			$endHasTime = $component->$endField->hasTime();
			$dueDate = $DTEND->format('Y-m-d');
			$timeEnd = $DTEND->format('H:i:s');
			if (!$endHasTime) {
				$endTime = strtotime('-1 day', strtotime($dueDate . ' ' . $timeEnd));
				$dueDate = date('Y-m-d', $endTime);
				$timeEnd = date('H:i:s', $endTime);
			}
		} else {
			$endTime = strtotime('+1 day', strtotime($dateStart . ' ' . $timeStart));
			$dueDate = date('Y-m-d', $endTime);
			$timeEnd = date('H:i:s', $endTime);
		}
		if (!$startHasTime && !$endHasTime) {
			$allday = 1;
			$currentUser = \App\User::getCurrentUserModel();
			$timeStart = $currentUser->getDetail('start_hour') . ':00';
			$timeEnd = $currentUser->getDetail('end_hour') . ':00';
		}

		return ['allday' => $allday, 'date_start' => $dateStart, 'due_date' => $dueDate, 'time_start' => $timeStart, 'time_end' => $timeEnd];
	}

	/**
	 * Get end field name.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function getEndFieldName($type)
	{
		return ($type == 'VEVENT') ? 'DTEND' : 'DUE';
	}

	/**
	 * Get state from dav.
	 *
	 * @param Sabre\VObject\Component $component
	 *
	 * @return string
	 */
	public function getStateFromDav(\Sabre\VObject\Component $component)
	{
		$state = '';
		if (isset($component->TRANSP)) {
			switch ($component->TRANSP->getValue()) {
				case 'OPAQUE':
					$state = 'PLL_OPAQUE';
					break;
				case 'TRANSPARENT':
					$state = 'PLL_TRANSPARENT';
					break;
			}
		}

		return $state;
	}

	/**
	 * Get state from crm.
	 *
	 * @param string $component
	 *
	 * @return string
	 */
	public function getStateFromCrm($component)
	{
		$state = '';
		switch ($component) {
			case 'PLL_OPAQUE':
				$state = 'OPAQUE';
				break;
			case 'PLL_TRANSPARENT':
				$state = 'TRANSPARENT';
				break;
		}

		return $state;
	}

	/**
	 * Get Visibility.
	 *
	 * @param Sabre\VObject\Component $component
	 *
	 * @return string
	 */
	public function getVisibility(Sabre\VObject\Component $component)
	{
		$visibility = 'Private';
		if (isset($component->CLASS)) {
			switch (strtolower($component->CLASS->getValue())) {
				case 'public':
					$visibility = 'Public';
					break;
				case 'private':
					$visibility = 'Private';
					break;
			}
		}

		return $visibility;
	}

	/**
	 * Get priority from dav.
	 *
	 * @param Sabre\VObject\Component $component
	 *
	 * @return string
	 */
	public function getPriorityFromDav(\Sabre\VObject\Component $component)
	{
		$values = [
			1 => 'High',
			5 => 'Medium',
			9 => 'Low',
		];
		$return = 'Medium';
		$value = isset($component->PRIORITY) ? \App\Purifier::purify($component->PRIORITY->getValue()) : false;
		if ($value && isset($values[$value])) {
			$return = $values[$value];
		}

		return $return;
	}

	/**
	 * Get priority from crm.
	 *
	 * @param string $component
	 *
	 * @return int
	 */
	public function getPriorityFromCrm($component)
	{
		$values = [
			'High' => 1,
			'Medium' => 5,
			'Low' => 9,
		];
		$return = 5;
		$value = $component;
		if ($value && isset($values[$value])) {
			$return = $values[$value];
		}

		return $return;
	}

	/**
	 * Get status from dav.
	 *
	 * @param Sabre\VObject\Component $component
	 * @param string                  $calType
	 *
	 * @return array
	 */
	public function getStatusFromDav(\Sabre\VObject\Component $component, $calType)
	{
		if ($calType === 'VEVENT') {
			$values = [
				'TENTATIVE' => 'PLL_PLANNED',
				'CANCELLED' => 'PLL_OVERDUE',
				'CANCELLED' => 'PLL_POSTPONED',
				'CANCELLED' => 'PLL_CANCELLED',
				'CONFIRMED' => 'PLL_COMPLETED',
			];
		} else {
			$values = [
				'NEEDS-ACTION' => 'PLL_PLANNED',
				'IN-PROCESS' => 'PLL_IN_REALIZATION',
				'CANCELLED' => 'PLL_OVERDUE',
				'CANCELLED' => 'PLL_POSTPONED',
				'CANCELLED' => 'PLL_CANCELLED',
				'COMPLETED' => 'PLL_COMPLETED',
			];
		}
		if (isset($component->STATUS)) {
			$value = strtoupper(\App\Purifier::purify($component->STATUS->getValue()));
		}
		$return = reset($values);
		if ($value && isset($values[$value])) {
			$return = $values[$value];
		}

		return $return;
	}

	/**
	 * Get status from crm.
	 *
	 * @param string $component
	 * @param string $calType
	 *
	 * @return array
	 */
	public function getStatusFromCrm($component, $calType)
	{
		if ($calType === 'VEVENT') {
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
		$value = $component;
		$return = reset($values);
		if ($value && isset($values[$value])) {
			$return = $values[$value];
		}

		return $return;
	}

	/**
	 * Get dav detail.
	 *
	 * @return array|bool
	 */
	public function getDavDetail()
	{
		return (new \App\Db\Query())->from('dav_calendarobjects')->where(['calendarid' => $this->calendarId, 'crmid' => $this->record['crmid']])->one();
	}

	/**
	 * Adds a change record to the addressbookchanges table.
	 *
	 * @param string $objectUri
	 * @param int    $operation 1 = add, 2 = modify, 3 = delete
	 */
	protected function addChange($objectUri, $operation)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$syncToken = (new \App\Db\Query())->select(['synctoken'])->from('dav_calendars')->where(['id' => $this->calendarId])->scalar();
		$dbCommand->insert('dav_calendarchanges', ['uri' => $objectUri, 'synctoken' => $syncToken, 'calendarid' => $this->calendarId, 'operation' => $operation])->execute();
		$dbCommand->update('dav_calendars', ['synctoken' => new \yii\db\Expression('synctoken + 1')], ['id' => $this->calendarId])->execute();
	}

	/**
	 * Record mark complete.
	 */
	protected function recordMarkComplete()
	{
		App\Db::getInstance()->createCommand()->update('vtiger_activity', [
			'dav_status' => 0,
			], ['activityid' => $this->record['crmid']]
		)->execute();
	}

	/**
	 * To delete.
	 *
	 * @param array $cal
	 *
	 * @return bool
	 */
	protected function toDelete($cal)
	{
		if ($cal['smownerid'] == '') {
			return true;
		}
		$accessibleGroups = \App\Fields\Owner::getInstance(false, $this->user)->getAccessibleGroups();
		$result = (new App\Db\Query())->select(['visibility'])->from('vtiger_activity')->innerJoin('vtiger_crmentity', 'vtiger_activity.activityid = vtiger_crmentity.crmid')->where(['activityid' => $cal['crmid'], 'vtiger_crmentity.deleted' => 0])->one();
		if (!$result) {
			return true;
		}
		$visibility = $result['visibility'];
		if ($cal['smownerid'] != $this->user->get('id') && (!array_key_exists($cal['smownerid'], $accessibleGroups)) && $visibility != 'Public') {
			return true;
		}

		return false;
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
	protected function getDenormalizedData($calendarData)
	{
		$vObject = Sabre\VObject\Reader::read($calendarData);
		$componentType = null;
		$component = null;
		$firstOccurence = null;
		$lastOccurence = null;
		$uid = null;
		foreach ($vObject->getComponents() as $component) {
			if ($component->name !== 'VTIMEZONE') {
				$componentType = $component->name;
				$uid = (string) $component->UID;
				break;
			}
		}
		if (!$componentType) {
			throw new \Sabre\DAV\Exception\BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
		}
		if ($componentType === 'VEVENT') {
			$firstOccurence = $component->DTSTART->getDateTime()->getTimeStamp();
			// Finding the last occurence is a bit harder
			if (!isset($component->RRULE)) {
				if (isset($component->DTEND)) {
					$lastOccurence = $component->DTEND->getDateTime()->getTimeStamp();
				} elseif (isset($component->DURATION)) {
					$endDate = clone $component->DTSTART->getDateTime();
					$endDate = $endDate->add(Sabre\VObject\DateTimeParser::parse($component->DURATION->getValue()));
					$lastOccurence = $endDate->getTimeStamp();
				} elseif (!$component->DTSTART->hasTime()) {
					$endDate = clone $component->DTSTART->getDateTime();
					$endDate = $endDate->modify('+1 day');
					$lastOccurence = $endDate->getTimeStamp();
				} else {
					$lastOccurence = $firstOccurence;
				}
			} else {
				$it = new Sabre\VObject\Recur\EventIterator($vObject, (string) $component->UID);
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
		}
		// Destroy circular references to PHP will GC the object.
		$vObject->destroy();

		return [
			'etag' => md5($calendarData),
			'size' => strlen($calendarData),
			'componentType' => $componentType,
			'firstOccurence' => $firstOccurence,
			'lastOccurence' => $lastOccurence,
			'uid' => $uid,
		];
	}

	/**
	 * Get vtime zone.
	 *
	 * @param Sabre\VObject\Component $vcalendar
	 * @param type                    $tzid
	 * @param int                     $from
	 * @param int                     $to
	 *
	 * @return bool
	 */
	public function getVTimeZone(Sabre\VObject\Component $vcalendar, $tzid, $from = 0, $to = 0)
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

		$vt = $vcalendar->createComponent('VTIMEZONE');
		$vt->TZID = $tz->getName();
		$vt->TZURL = 'http://tzurl.org/zoneinfo/' . $tz->getName();
		$vt->add('X-LIC-LOCATION', $tz->getName());
		$std = null;
		$dst = null;
		foreach ($transitions as $i => $trans) {
			$cmp = null;

			// skip the first entry...
			if ($i == 0) {
				// ... but remember the offset for the next TZOFFSETFROM value
				$tzfrom = $trans['offset'] / 3600;
				continue;
			}

			// daylight saving time definition
			if ($trans['isdst']) {
				$t_dst = $trans['ts'];
				$dst = $vcalendar->createComponent('DAYLIGHT');
				$cmp = $dst;
				$cmpName = 'DAYLIGHT';
			}
			// standard time definition
			else {
				$t_std = $trans['ts'];
				$std = $vcalendar->createComponent('STANDARD');
				$cmp = $std;
				$cmpName = 'STANDARD';
			}
			if ($cmp && empty($vt->select($cmpName))) {
				$dt = new DateTime($trans['time']);
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
		$microsoftExchangeMap = array_flip(Sabre\VObject\TimeZoneUtil::$microsoftExchangeMap);
		if (array_key_exists($tz->getName(), $microsoftExchangeMap)) {
			$vt->add('X-MICROSOFT-CDO-TZID', $microsoftExchangeMap[$tz->getName()]);
		}

		return $vt;
	}

	/**
	 * Record save attendee.
	 *
	 * @param Vtiger_Record_Model            $record
	 * @param Sabre\VObject\Component\VEvent $component
	 */
	protected function recordSaveAttendee(Vtiger_Record_Model $record, Sabre\VObject\Component\VEvent $component)
	{
		$query = (new \App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => $record->getId()]);
		$invities = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (!empty($row['email'])) {
				$invities[$row['email']] = $row;
			}
		}
		$dataReader->close();
		$time = Sabre\VObject\DateTimeParser::parse($component->DTSTAMP);
		$timeFormated = $time->format('Y-m-d H:i:s');
		$db = \App\Db::getInstance();
		$dbCommand = $db->createCommand();
		$attendees = $component->select('ATTENDEE');
		foreach ($attendees as &$attendee) {
			$value = ltrim($attendee->getValue(), 'mailto:');
			if ($attendee['ROLE']->getValue() === 'CHAIR') {
				$users = App\Fields\Email::findCrmidByEmail($value, ['Users']);
				if (!empty($users)) {
					continue;
				}
			}
			$crmid = 0;
			$records = App\Fields\Email::findCrmidByEmail($value, array_keys(array_merge(\App\ModuleHierarchy::getModulesByLevel(), \App\ModuleHierarchy::getModulesByLevel(3))));
			if (!empty($records)) {
				$record = reset($records);
				$crmid = $record['crmid'];
			}
			$status = $this->getAttendeeStatus($attendee['PARTSTAT']->getValue());
			if (isset($invities[$value])) {
				$row = $invities[$value];
				if ($row['status'] !== $status) {
					$dbCommand->update('u_#__activity_invitation', [
						'status' => $status,
						'time' => $timeFormated,
						], ['activityid' => $record->getId(), 'email' => $value]
					)->execute();
				}
				unset($invities[$value]);
			} else {
				$params = [
					'email' => $value,
					'crmid' => $crmid,
					'status' => $status,
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

	/**
	 * Dav save attendee.
	 *
	 * @param array                             $record
	 * @param Sabre\VObject\Component\VCalendar $vcalendar
	 * @param Sabre\VObject\Component\VEvent    $component
	 */
	protected function davSaveAttendee(array $record, Sabre\VObject\Component\VCalendar $vcalendar, Sabre\VObject\Component\VEvent $component)
	{
		$owner = Users_Privileges_Model::getInstanceById($record['smownerid']);

		$invities = [];
		$query = (new App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => $record['activityid']]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (!empty($row['email'])) {
				$invities[$row['email']] = $row;
			}
		}
		$dataReader->close();
		$attendees = $component->select('ATTENDEE');
		if (empty($attendees)) {
			if (!empty($invities)) {
				$organizer = $vcalendar->createProperty('ORGANIZER', 'mailto:' . $owner->get('email1'));
				$organizer->add('CN', $owner->getName());
				$component->add($organizer);
				$attendee = $vcalendar->createProperty('ATTENDEE', 'mailto:' . $owner->get('email1'));
				$attendee->add('CN', $owner->getName());
				$attendee->add('ROLE', 'CHAIR');
				$attendee->add('PARTSTAT', 'ACCEPTED');
				$attendee->add('RSVP', 'false');
				$component->add($attendee);
			}
		} else {
			foreach ($attendees as &$attendee) {
				$value = ltrim($attendee->getValue(), 'mailto:');
				if (isset($invities[$value])) {
					$row = $invities[$value];
					$attendee['PARTSTAT']->setValue($this->getAttendeeStatus($row['status'], false));
					unset($invities[$value]);
				} else {
					$component->remove($attendee);
				}
			}
		}
		foreach ($invities as &$row) {
			$attendee = $vcalendar->createProperty('ATTENDEE', 'mailto:' . $row['email']);
			$attendee->add('CN', vtlib\Functions::getCRMRecordLabel($row['crmid']));
			$attendee->add('ROLE', 'REQ-PARTICIPANT');
			$attendee->add('PARTSTAT', $this->getAttendeeStatus($row['status'], false));
			$attendee->add('RSVP', $row['status'] == '0' ? 'true' : 'false');
			$component->add($attendee);
		}
	}

	/**
	 * Get attendee status.
	 *
	 * @param string $value
	 * @param bool   $toCrm
	 *
	 * @return string
	 */
	public function getAttendeeStatus($value, $toCrm = true)
	{
		$statuses = ['NEEDS-ACTION', 'ACCEPTED', 'DECLINED'];

		if ($toCrm) {
			$status = 0;
			$statuses = array_flip($statuses);
		} else {
			$status = 'NEEDS-ACTION';
		}
		if (isset($statuses[$value])) {
			$status = $statuses[$value];
		}

		return $status;
	}
}
