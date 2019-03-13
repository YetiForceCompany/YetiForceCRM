<?php

/**
 * Api CalDAV Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	 * @var bool|mixed
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
	 * Max date.
	 *
	 * @var string
	 */
	const MAX_DATE = '2038-01-01';
	/**
	 * Cache.
	 *
	 * @var array
	 */
	protected static $cache = [];

	/**
	 * calDavCrm2Dav.
	 */
	public function calDavCrm2Dav()
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		$queryGenerator = new \App\QueryGenerator('Calendar');
		$queryGenerator->setFields(['id', 'subject', 'subject', 'activitytype', 'date_start', 'due_date', 'time_start', 'time_end', 'activitystatus', 'taskpriority', 'location', 'visibility', 'assigned_user_id', 'allday', 'state', 'createdtime', 'modifiedtime', 'description']);
		$queryGenerator->setCustomColumn(['vtiger_crmentity.deleted']);
		$queryGenerator->permissions = false;
		$query = $queryGenerator->createQuery();
		$query->where(['vtiger_crmentity.deleted' => 0, 'vtiger_activity.dav_status' => 1]);
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
		$create = $updates = 0;
		foreach ($this->davUsers as $userId => $user) {
			$this->calendarId = $user->get('calendarsid');
			$this->user = $user;
			$isPermitted = !isset(self::$cache[$userId][$this->record['id']]) && !$this->toDelete($this->record);
			if ($isPermitted) {
				$exclusion = \App\Config::component('Dav', 'CALDAV_EXCLUSION_TO_DAV');
				if (false !== $exclusion) {
					foreach ($exclusion as $key => $value) {
						if ($this->record[$key] == $value) {
							$isPermitted = false;
						}
					}
				}
				if ($isPermitted) {
					$orgUserId = App\User::getCurrentUserId();
					App\User::setCurrentUserId($userId);
					$event = $this->getDavDetail();
					if (false === $event) {
						// Creating
						$this->davCreate();
						++$create;
					} elseif (strtotime($this->record['modifiedtime']) > $event['lastmodified']) { // Updating
						$this->davUpdate($event);
						++$updates;
					}
					App\User::setCurrentUserId($orgUserId);
					self::$cache[$userId][$this->record['id']] = true;
				}
			}
		}
		$this->recordMarkComplete();
		\App\Log::trace("Calendar end - CRM >> DAV | create: $create | updates: $updates", __METHOD__);
	}

	/**
	 * Dav create.
	 */
	public function davCreate()
	{
		\App\Log::trace(__METHOD__ . ' | Start CRM ID:' . $this->record['id']);
		$instance = \App\Integrations\Dav\Calendar::createEmptyInstance();
		$instance->loadFromArray($this->record);
		$component = $instance->createComponent();
		$calendar = $instance->getVCalendar();
		$uid = (string) $component->UID;
		$calUri = $uid . '.ics';
		if ('VEVENT' === (string) $component->name) {
			$this->davSaveAttendee($this->record, $calendar, $component);
		}
		$calendarData = $calendar->serialize();
		$extraData = $this->getDenormalizedData($calendarData);
		\App\Db::getInstance()->createCommand()->insert('dav_calendarobjects', [
			'calendarid' => $this->calendarId,
			'uri' => $calUri,
			'calendardata' => $calendarData,
			'lastmodified' => strtotime($this->record['modifiedtime']),
			'etag' => $extraData['etag'],
			'size' => $extraData['size'],
			'componenttype' => $extraData['componentType'],
			'firstoccurence' => $extraData['firstOccurence'],
			'lastoccurence' => $extraData['lastOccurence'],
			'uid' => $uid,
			'crmid' => $this->record['id'],
		])->execute();
		\App\Integrations\Dav\Calendar::addChange($this->calendarId, $calUri, 1);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Dav update.
	 *
	 * @param array $dav
	 */
	public function davUpdate($dav)
	{
		$instance = \App\Integrations\Dav\Calendar::loadFromDav($dav['calendardata']);
		$instance->loadFromArray($this->record);
		$component = $instance->getComponent();
		$instance->updateComponent();
		$calendar = $instance->getVCalendar();
		if ('VEVENT' === (string) $component->name) {
			$this->davSaveAttendee($this->record, $calendar, $component);
		}
		$calendarData = $calendar->serialize();
		$extraData = $this->getDenormalizedData($calendarData);
		\App\Db::getInstance()->createCommand()->update('dav_calendarobjects', [
			'calendardata' => $calendarData,
			'lastmodified' => strtotime($this->record['modifiedtime']),
			'etag' => $extraData['etag'],
			'size' => $extraData['size'],
			'componenttype' => $extraData['componentType'],
			'firstoccurence' => $extraData['firstOccurence'],
			'lastoccurence' => $extraData['lastOccurence'],
			'uid' => $extraData['uid'],
			'crmid' => $this->record['id'],
		], ['id' => $dav['id']]
		)->execute();
		\App\Integrations\Dav\Calendar::addChange($this->calendarId, $dav['uri'], 2);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Dav delete.
	 *
	 * @param $calendar
	 *
	 * @throws \yii\db\Exception
	 */
	public function davDelete($calendar)
	{
		\App\Log::trace(__METHOD__ . ' | Start Calendar ID:' . $calendar['id']);
		\App\Integrations\Dav\Calendar::addChange($this->calendarId, $calendar['uri'], 3);
		\App\Db::getInstance()->createCommand()->delete('dav_calendarobjects', ['id' => $calendar['id']])->execute();
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Cal dav to crm.
	 */
	public function calDav2Crm()
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		foreach ($this->davUsers as $user) {
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
		\App\Log::trace('Start', __METHOD__);
		$query = (new \App\Db\Query())->select(['dav_calendarobjects.*', 'vtiger_crmentity.modifiedtime', 'vtiger_crmentity.setype', 'assigned_user_id' => 'vtiger_crmentity.smownerid', 'vtiger_crmentity.crmid', 'vtiger_crmentity.deleted', 'vtiger_activity.visibility'])->from('dav_calendarobjects')->leftJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = dav_calendarobjects.crmid')->leftJoin('vtiger_activity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')->where(['calendarid' => $this->calendarId]);
		$skipped = $create = $deletes = $updates = 0;
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (!$row['crmid']) { //Creating
				if ($this->recordCreate($row)) {
					++$create;
				} else {
					++$skipped;
				}
			} elseif ($this->toDelete(array_merge($row, ['id' => $row['crmid']]))) { // Deleting
				$this->davDelete($row);
				++$deletes;
			} else {
				if (strtotime($row['modifiedtime']) < $row['lastmodified']) { // Updating
					if ($this->recordUpdate(Vtiger_Record_Model::getInstanceById($row['crmid'], $row['setype']), $row)) {
						++$updates;
					} else {
						++$skipped;
					}
				}
			}
		}
		$dataReader->close();
		\App\Log::trace("Calendar end - DAV >> CRM | create: $create | deletes: $deletes | updates: $updates | skipped: $skipped", __METHOD__);
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
		$calendar = \App\Integrations\Dav\Calendar::loadFromContent($cal['calendardata']);
		foreach ($calendar->getRecordInstance() as $recordModel) {
			$component = $calendar->getComponent();
			$recordModel->set('assigned_user_id', $this->user->get('id'));
			$exclusion = \App\Config::component('Dav', 'CALDAV_EXCLUSION_FROM_DAV');
			if (false !== $exclusion) {
				foreach ($exclusion as $key => $value) {
					if ($recordModel->get($key) == $value) {
						\App\Log::info(__METHOD__ . ' | End exclusion');
						return false;
					}
				}
			}
			$recordModel->save();
			$dbCommand = \App\Db::getInstance()->createCommand();
			$dbCommand->update('dav_calendarobjects', [
				'crmid' => $recordModel->getId(),
			], ['id' => $cal['id']]
			)->execute();
			$dbCommand->update('vtiger_crmentity', [
				'modifiedtime' => date('Y-m-d H:i:s', $cal['lastmodified']),
			], ['crmid' => $recordModel->getId()]
			)->execute();
			if ('VEVENT' === (string) $component->name) {
				$this->recordSaveAttendee($recordModel, $component);
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
		\App\Log::trace(__METHOD__ . ' | Start Cal ID:' . $cal['crmid']);
		$calendar = \App\Integrations\Dav\Calendar::loadFromContent($cal['calendardata'], $record, $cal['uid']);
		foreach ($calendar->getRecordInstance() as $recordModel) {
			$component = $calendar->getComponent();
			$recordModel->set('assigned_user_id', $this->user->get('id'));
			$exclusion = \App\Config::component('Dav', 'CALDAV_EXCLUSION_FROM_DAV');
			if (false !== $exclusion) {
				foreach ($exclusion as $key => $value) {
					if ($recordModel->get($key) == $value) {
						\App\Log::info(__METHOD__ . ' | End exclusion');
						return false;
					}
				}
			}
			$recordModel->save();
			$dbCommand = \App\Db::getInstance()->createCommand();
			$dbCommand->update('dav_calendarobjects', [
				'crmid' => $recordModel->getId(),
			], ['id' => $cal['id']]
			)->execute();
			$dbCommand->update('vtiger_crmentity', [
				'modifiedtime' => date('Y-m-d H:i:s', $cal['lastmodified']),
			], ['crmid' => $recordModel->getId()]
			)->execute();
			if ('VEVENT' === (string) $component->name) {
				$this->recordSaveAttendee($recordModel, $component);
			}
		}
		\App\Log::trace(__METHOD__ . ' | End');
		return true;
	}

	/**
	 * Get dav detail.
	 *
	 * @return array|bool
	 */
	public function getDavDetail()
	{
		return (new \App\Db\Query())->from('dav_calendarobjects')->where(['calendarid' => $this->calendarId, 'crmid' => $this->record['id']])->one();
	}

	/**
	 * Record mark complete.
	 */
	protected function recordMarkComplete()
	{
		App\Db::getInstance()->createCommand()->update('vtiger_activity', [
			'dav_status' => 0,
		], ['activityid' => $this->record['id']]
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
		if ('' === $cal['assigned_user_id'] || 0 !== (int) $cal['deleted']) {
			return true;
		}
		$userId = (int) $this->user->getId();
		switch ($this->user->get('sync_caldav')) {
			case 'PLL_OWNER_PERSON':
				$isPermitted = (int) $cal['assigned_user_id'] === $userId || in_array($userId, \App\Fields\SharedOwner::getById($cal['id']));
				break;
			case 'PLL_OWNER_PERSON_GROUP':
				$shownerIds = \App\Fields\SharedOwner::getById($cal['id']);
				$isPermitted = (int) $cal['assigned_user_id'] === $userId || in_array($cal['assigned_user_id'], $this->user->get('groups')) || in_array($userId, $shownerIds) || count(array_intersect($shownerIds, $this->user->get('groups'))) > 0;
				break;
			default:
			case 'PLL_OWNER':
				$isPermitted = (int) $cal['assigned_user_id'] === $userId;
				break;
		}
		if (!$isPermitted && 'Public' !== $cal['visibility']) {
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
			'size' => strlen($calendarData),
			'componentType' => $componentType,
			'firstOccurence' => $firstOccurence,
			'lastOccurence' => $lastOccurence,
			'uid' => $uid,
		];
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
		$dbCommand = \App\Db::getInstance()->createCommand();
		$attendees = $component->select('ATTENDEE');
		foreach ($attendees as &$attendee) {
			$nameAttendee = isset($attendee->parameters['CN']) ? $attendee->parameters['CN']->getValue() : null;
			$value = $attendee->getValue();
			if (0 === strpos($value, 'mailto:')) {
				$value = substr($value, 7, strlen($value) - 7);
			}
			if (\App\TextParser::getTextLength($value) > 100 || !\App\Validator::email($value)) {
				throw new \Sabre\DAV\Exception\BadRequest('Invalid email');
			}
			if ('CHAIR' === $attendee['ROLE']->getValue()) {
				$users = \App\Fields\Email::findCrmidByEmail($value, ['Users']);
				if (!empty($users)) {
					continue;
				}
			}
			$crmid = 0;
			$records = \App\Fields\Email::findCrmidByEmail($value, array_keys(array_merge(\App\ModuleHierarchy::getModulesByLevel(), \App\ModuleHierarchy::getModulesByLevel(4))));
			if (!empty($records)) {
				$recordCrm = current($records);
				$crmid = $recordCrm['crmid'];
			}
			$status = $this->getAttendeeStatus($attendee['PARTSTAT']->getValue());
			if (isset($invities[$value])) {
				$row = $invities[$value];
				if ($row['status'] !== $status || $row['name'] !== $nameAttendee) {
					$dbCommand->update('u_#__activity_invitation', [
						'status' => $status,
						'time' => $timeFormated,
						'name' => \App\TextParser::textTruncate($nameAttendee, 500, false),
					], ['activityid' => $record->getId(), 'email' => $value]
					)->execute();
				}
				unset($invities[$value]);
			} else {
				$params = [
					'email' => $value,
					'crmid' => $crmid,
					'status' => $status,
					'name' => \App\TextParser::textTruncate($nameAttendee, 500, false),
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
		$owner = Users_Privileges_Model::getInstanceById($record['assigned_user_id']);
		$invities = [];
		$query = (new App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => $record['id']]);
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
			$attendee->add('CN', empty($row['crmid']) ? $row['name'] : \App\Record::getLabel($row['crmid']));
			$attendee->add('ROLE', 'REQ-PARTICIPANT');
			$attendee->add('PARTSTAT', $this->getAttendeeStatus($row['status'], false));
			$attendee->add('RSVP', '0' == $row['status'] ? 'true' : 'false');
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
