<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class API_CalDAV_Model
{

	const PRODID = 'YetiForce';
	const CALENDAR_NAME = 'YFCalendar';
	const COMPONENTS = 'VEVENT,VTODO';

	public $pdo = false;
	public $log = false;
	public $user = false;
	public $record = false;
	public $calendarId = false;
	public $davUsers = [];
	protected $crmRecords = [];

	function __construct()
	{
		$dbconfig = vglobal('dbconfig');
		$this->pdo = new PDO('mysql:host=' . $dbconfig['db_server'] . ';dbname=' . $dbconfig['db_name'] . ';charset=utf8', $dbconfig['db_username'], $dbconfig['db_password']);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// Autoloader
		require_once 'libraries/SabreDAV/autoload.php';
	}

	public function calDavCrm2Dav()
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$result = $this->getCrmRecordsToSync();
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$this->record = $db->raw_query_result_rowdata($result, $i);
			$this->saveCalendar();
		}
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function saveCalendar()
	{
		foreach ($this->davUsers as $key => $user) {
			$this->calendarId = $user->get('calendarsid');
			$current_user = vglobal('current_user');
			$current_user = $user;
			$accessibleGroups = $user->getAccessibleGroups();
			if ($this->record['smownerid'] == $user->get('id') || $this->record['visibility'] == 'Public' || array_key_exists($this->record['smownerid'], $accessibleGroups)) {
				$vcalendar = $this->getCalendarDetail();
				if ($vcalendar == false) { // Creating
					$this->createCalendar();
					//} elseif($this->record['deleted'] == 1){
				} elseif (strtotime($this->record['modifiedtime']) > $vcalendar['lastmodified']) { // Updating
					$this->updateCalendar($vcalendar);
				}
			}
		}
		$this->markComplete();
	}

	public function createCalendar()
	{
		$record = $this->record;
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start CRM ID:' . $record['crmid']);
		$calType = $record['activitytype'] == 'Task' ? 'VTODO' : 'VEVENT';
		$endField = $this->getEndFieldName($calType);

		$vcalendar = new Sabre\VObject\Component\VCalendar();
		$vcalendar->PRODID = '-//' . self::PRODID . ' V' . vglobal('YetiForce_current_version') . '//';
		$start = $record['date_start'] . ' ' . $record['time_start'];
		$end = $record['due_date'] . ' ' . $record['time_end'];
		if ($record['allday']) {
			$startDT = new \DateTime($start);
			$DTSTART = $vcalendar->createProperty('DTSTART', $startDT);
			$endDT = new DateTime($end);
			$endDT->modify('+1 day');
			$DTEND = $vcalendar->createProperty($endField, $endDT);
			$DTEND['VALUE'] = 'DATE';
			$DTSTART['VALUE'] = 'DATE';
		} else {
			$startDT = $DTSTART = new \DateTime($start);
			$endDT = $DTEND = new \DateTime($end);
		}
		$cal = $vcalendar->createComponent($calType);
		//$cal->add($vcalendar->createProperty('CREATED', new \DateTime($record['createdtime'])));
		//$cal->add($vcalendar->createProperty('LAST-MODIFIED', new \DateTime($record['modifiedtime'])));
		$cal->add($vcalendar->createProperty('SUMMARY', $record['subject']));
		$cal->add($vcalendar->createProperty('DTSTART', $DTSTART));
		$cal->add($vcalendar->createProperty($endField, $DTEND));
		$cal->add($vcalendar->createProperty('CLASS', $record['visibility'] == 'Private' ? 'PRIVATE' : 'PUBLIC'));
		$cal->add($vcalendar->createProperty('PRIORITY', $this->getPriority($record['priority'], FALSE)));
		$state = $this->getState($record['state']);
		if ($state)
			$cal->add($vcalendar->createProperty('TRANSP', $state, FALSE));

		if (!empty($record['location']))
			$cal->add($vcalendar->createProperty('LOCATION', $record['location']));
		if (!empty($record['description']))
			$cal->add($vcalendar->createProperty('DESCRIPTION', $record['description']));
		$vcalendar->add($cal);
		$dtz = date_default_timezone_get();
		$vTimeZone = self::getVTimeZone($vcalendar, $dtz, $startDT->getTimestamp(), $endDT->getTimestamp());
		$vcalendar->add($vTimeZone);
		$calendarData = $vcalendar->serialize();

		$modifiedtime = strtotime($record['modifiedtime']);
		$extraData = $this->getDenormalizedData($calendarData);
		$calUri = date('Y-m-d\THis') . '-' . $record['crmid'];
		$stmt = $this->pdo->prepare('INSERT INTO dav_calendarobjects (calendarid, uri, calendardata, lastmodified, etag, size, componenttype, firstoccurence, lastoccurence, uid, crmid) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
		$stmt->execute([
			$this->calendarId,
			$calUri . '.ics',
			$calendarData,
			$modifiedtime,
			$extraData['etag'],
			$extraData['size'],
			$extraData['componentType'],
			$extraData['firstOccurence'],
			$extraData['lastOccurence'],
			$calUri,
			$record['crmid']
		]);
		$this->addChange($calUri, 1);
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function updateCalendar($calendar)
	{
		$record = $this->record;
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start CRM ID:' . $record['crmid']);
		$calType = $record['activitytype'] == 'Task' ? 'VTODO' : 'VEVENT';
		$endField = $this->getEndFieldName($calType);

		$vcalendar = Sabre\VObject\Reader::read($calendar['calendardata']);
		$vcalendar->PRODID = '-//' . self::PRODID . ' V' . vglobal('YetiForce_current_version') . '//';
		$start = $record['date_start'] . ' ' . $record['time_start'];
		$end = $record['due_date'] . ' ' . $record['time_end'];
		if ($record['allday']) {
			$startDT = new \DateTime($start);
			$DTSTART = $vcalendar->createProperty('DTSTART', $startDT);
			$endDT = new DateTime($end);
			$endDT->modify('+1 day');
			$DTEND = $vcalendar->createProperty($endField, $endDT);
			$DTSTART['VALUE'] = 'DATE';
			$DTEND['VALUE'] = 'DATE';
		} else {
			$DTSTART = new \DateTime($start);
			$DTEND = new \DateTime($end);
		}
		foreach ($vcalendar->getBaseComponents() as $component) {
			if ($component->name = $calType) {
				$component->__set('LAST-MODIFIED', $vcalendar->createProperty('LAST-MODIFIED', new DateTime($record['modifiedtime'])));
				$component->DTSTART = $DTSTART;
				$component->$endField = $DTEND;
				$component->SUMMARY = $record['subject'];
				$component->LOCATION = $record['location'];
				$component->DESCRIPTION = $record['description'];
				$component->CLASS = $record['visibility'] == 'Private' ? 'PRIVATE' : 'PUBLIC';
				$component->PRIORITY = $this->getPriority($record['priority'], FALSE);
				$state = $this->getState($record['state'], FALSE);
				if ($state)
					$component->TRANSP = $state;
			}
		}
		unset($vcalendar->VTIMEZONE);
		$dtz = date_default_timezone_get();
		$vTimeZone = self::getVTimeZone($vcalendar, $dtz, $startDT->getTimestamp(), $endDT->getTimestamp());
		$vcalendar->add($vTimeZone);
		$calendarData = $vcalendar->serialize();
		$modifiedtime = strtotime($record['modifiedtime']);
		$extraData = $this->getDenormalizedData($calendarData);
		$stmt = $this->pdo->prepare('UPDATE dav_calendarobjects SET calendardata = ?, lastmodified = ?, etag = ?, size = ?, componenttype = ?, firstoccurence = ?, lastoccurence = ?, uid = ?, crmid = ? WHERE id = ?');
		$stmt->execute([$calendarData, $modifiedtime, $extraData['etag'], $extraData['size'], $extraData['componentType'], $extraData['firstOccurence'], $extraData['lastOccurence'], $extraData['uid'], $record['crmid'], $calendar['id']]);
		$this->addChange($calendar['uri'], 2);
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function deletedCal($calendar)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start Calendar ID:' . $card['id']);
		$this->addChange($calendar['uri'], 3);
		$stmt = $this->pdo->prepare('DELETE FROM dav_calendarobjects WHERE id = ?;');
		$stmt->execute([
			$calendar['id']
		]);
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function calDav2Crm()
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		foreach ($this->davUsers as $key => $user) {
			$this->calendarId = $user->get('calendarsid');
			$this->user = $user;
			$current_user = vglobal('current_user');
			$current_user = $user;
			$this->syncDavCalendar();
		}
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function syncDavCalendar()
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$result = $this->getDavCardsToSync();
		$create = $deletes = $updates = 0;
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$cal = $db->raw_query_result_rowdata($result, $i);
			if (!$cal['crmid']) { //Creating
				$this->createRecord($cal);
				$create++;
			} elseif ($this->toDelete($cal)) {
				// Deleting $cal['crmid']
				$this->deletedCal($cal);
				$deletes++;
			} else {
				$crmLMT = strtotime($cal['modifiedtime']);
				$cardLMT = $cal['lastmodified'];
				if ($crmLMT < $cardLMT) { // Updating
					$recordModel = Vtiger_Record_Model::getInstanceById($cal['crmid']);
					$this->updateRecord($recordModel, $cal);
					$updates++;
				}
			}
		}
		$this->log->info("calDav2Crm | create: $create | deletes: $deletes | updates: $updates");
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function createRecord($cal)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start Cal ID' . $cal['id']);
		$vcalendar = Sabre\VObject\Reader::read($cal['calendardata']);
		foreach ($vcalendar->getBaseComponents() as $component) {
			if (in_array($component->name, ['VTODO', 'VEVENT'])) {
				$dates = $this->getEventDates($component);
				$rekord = Vtiger_Record_Model::getCleanInstance('Calendar');
				$rekord->set('assigned_user_id', $this->user->get('id'));
				$rekord->set('subject', $component->SUMMARY);
				$rekord->set('location', $component->LOCATION);
				$rekord->set('description', $component->DESCRIPTION);
				$rekord->set('allday', $dates['allday']);
				$rekord->set('date_start', $dates['date_start']);
				$rekord->set('due_date', $dates['due_date']);
				$rekord->set('time_start', $dates['time_start']);
				$rekord->set('time_end', $dates['time_end']);
				$rekord->set('activitystatus', $this->getStatus($component));
				if ($component->name == 'VTODO') {
					$rekord->set('activitytype', 'Task');
				} else {
					$rekord->set('activitytype', 'Meeting');
				}
				$rekord->set('taskpriority', $this->getPriority($component));
				$rekord->set('visibility', $this->getVisibility($component));
				$rekord->set('state', $this->getState($component));
				$rekord->save();
				$stmt = $this->pdo->prepare('UPDATE dav_calendarobjects SET crmid = ? WHERE id = ?;');
				$stmt->execute([
					$rekord->getId(),
					$cal['id']
				]);
				$stmt = $this->pdo->prepare('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?;');
				$stmt->execute([
					date('Y-m-d H:i:s', $cal['lastmodified']),
					$rekord->getId()
				]);
			}
		}
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function updateRecord($rekord, $cal)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start Cal ID:' . $card['id']);
		$vcalendar = Sabre\VObject\Reader::read($cal['calendardata']);
		foreach ($vcalendar->getBaseComponents() as $component) {
			if (in_array($component->name, ['VTODO', 'VEVENT'])) {
				$dates = $this->getEventDates($component);
				$rekord->set('mode', 'edit');
				$rekord->set('assigned_user_id', $this->user->get('id'));
				$rekord->set('subject', $component->SUMMARY);
				$rekord->set('location', $component->LOCATION);
				$rekord->set('description', $component->DESCRIPTION);
				$rekord->set('allday', $dates['allday']);
				$rekord->set('date_start', $dates['date_start']);
				$rekord->set('due_date', $dates['due_date']);
				$rekord->set('time_start', $dates['time_start']);
				$rekord->set('time_end', $dates['time_end']);
				$rekord->set('activitystatus', $this->getStatus($component));
				if ($component->name == 'VTODO') {
					$rekord->set('activitytype', 'Task');
				} else {
					$rekord->set('activitytype', 'Meeting');
				}
				$rekord->set('taskpriority', $this->getPriority($component));
				$rekord->set('visibility', $this->getVisibility($component));
				$rekord->set('state', $this->getState($component));
				$rekord->save();
				$stmt = $this->pdo->prepare('UPDATE dav_calendarobjects SET crmid = ? WHERE id = ?;');
				$stmt->execute([
					$rekord->getId(),
					$cal['id']
				]);
				$stmt = $this->pdo->prepare('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?;');
				$stmt->execute([
					date('Y-m-d H:i:s', $cal['lastmodified']),
					$rekord->getId()
				]);
			}
		}
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function getEventDates($component)
	{
		$allday = 0;
		$endField = $this->getEndFieldName($component->name);
		// Start
		if (isset($component->DTSTART)) {
			$DTSTART = Sabre\VObject\DateTimeParser::parse($component->DTSTART);
			$date_start = $DTSTART->format('Y-m-d');
			$time_start = $DTSTART->format('H:i:s');
			$startHasTime = $component->DTSTART->hasTime();
		} else {
			$DTSTAMP = Sabre\VObject\DateTimeParser::parse($component->DTSTAMP);
			$date_start = $DTSTAMP->format('Y-m-d');
			$time_start = $DTSTAMP->format('H:i:s');
		}
		//End
		if (isset($component->$endField)) {
			$DTEND = Sabre\VObject\DateTimeParser::parse($component->$endField);
			$endHasTime = $component->$endField->hasTime();
			if (!$endHasTime) {
				$DTEND->modify('-1 day');
			}
			$due_date = $DTEND->format('Y-m-d');
			$time_end = $DTEND->format('H:i:s');
		} else {
			$endTime = strtotime('+7 day', strtotime($date_start . ' ' . $time_start));
			$due_date = date('Y-m-d', $endTime);
			$time_end = date('H:i:s', $endTime);
		}
		if (!$startHasTime && !$endHasTime) {
			$allday = 1;
		}
		return ['allday' => $allday, 'date_start' => $date_start, 'due_date' => $due_date, 'time_start' => $time_start, 'time_end' => $time_end];
	}

	public function getEndFieldName($type)
	{
		return ($type == 'VEVENT') ? 'DTEND' : 'DUE';
	}

	public function getState($component, $cal = true)
	{
		$state = '';
		if ($cal) {
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
		} else {
			switch ($component) {
				case 'PLL_OPAQUE':
					$state = 'OPAQUE';
					break;
				case 'PLL_TRANSPARENT':
					$state = 'TRANSPARENT';
					break;
			}
		}
		return $state;
	}

	public function getVisibility($component)
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

	public function getPriority($component, $cal = true)
	{
		if ($cal) {
			$priority = 'Medium';
			if (isset($component->PRIORITY)) {
				switch ($component->PRIORITY->getValue()) {
					case 1:
						$priority = 'High';
						break;
					case 9:
						$priority = 'Low';
						break;
				}
			}
		} else {
			$priority = 5;
			switch ($component) {
				case 'High':
					$priority = 1;
					break;
				case 'Low':
					$priority = 9;
					break;
			}
		}
		return $priority;
	}

	public function getStatus($component)
	{
		if ($component->name == 'VTODO') {
			$status = 'Not Started';
			if (isset($component->STATUS)) {
				switch ($component->STATUS->getValue()) {
					case 'NEEDS-ACTION':
						$status = 'Pending Input';
						break;
					case 'IN-PROCESS':
						$status = 'In Progress';
						break;
					case 'COMPLETED':
						$status = 'Completed';
						break;
					case 'CANCELLED':
						$status = 'Deferred';
						break;
				}
			}
		} else {
			$status = 'Planned';
		}
		return $status;
	}

	public function getCrmRecordsToSync()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT vtiger_activity.*, vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.deleted, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_crmentity.description '
			. 'FROM vtiger_activity '
			. 'INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid '
			. "WHERE vtiger_crmentity.deleted=0 AND vtiger_activity.activityid > 0 AND vtiger_activity.activitytype IN ('Task','Meeting') AND vtiger_activity.dav_status = 1;";
		$result = $db->query($query);
		return $result;
	}

	public function getCalendarDetail()
	{
		$db = PearDatabase::getInstance();
		$sql = "SELECT * FROM dav_calendarobjects WHERE calendarid = ? AND crmid = ?;";
		$result = $db->pquery($sql, [$this->calendarId, $this->record['crmid']]);
		return $db->num_rows($result) > 0 ? $db->raw_query_result_rowdata($result, 0) : false;
	}

	public function getDavCardsToSync()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT dav_calendarobjects.*, vtiger_crmentity.modifiedtime, vtiger_crmentity.setype, vtiger_crmentity.smownerid FROM dav_calendarobjects LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = dav_calendarobjects.crmid WHERE calendarid = ?';
		$result = $db->pquery($query, [$this->calendarId]);
		return $result;
	}

	/**
	 * Adds a change record to the addressbookchanges table.
	 *
	 * @param mixed $addressBookId
	 * @param string $objectUri
	 * @param int $operation 1 = add, 2 = modify, 3 = delete
	 * @return void
	 */
	protected function addChange($objectUri, $operation)
	{
		/*
		  $stmt = $this->pdo->prepare('DELETE FROM dav_calendarchanges WHERE uri = ? AND calendarid = ?;');
		  $stmt->execute([
		  $objectUri,
		  $this->calendarId
		  ]);
		 */
		$stmt = $this->pdo->prepare('INSERT INTO dav_calendarchanges (uri, synctoken, calendarid, operation) SELECT ?, synctoken, ?, ? FROM dav_calendars WHERE id = ?');
		$stmt->execute([
			$objectUri,
			$this->calendarId,
			$operation,
			$this->calendarId,
		]);
		$stmt = $this->pdo->prepare('UPDATE dav_calendars SET synctoken = synctoken + 1 WHERE id = ?');
		$stmt->execute([
			$this->calendarId,
		]);
	}

	protected function markComplete()
	{
		$query = 'UPDATE vtiger_activity SET dav_status = ? WHERE activityid = ?;';
		$stmt = $this->pdo->prepare($query);
		$stmt->execute([ 0, $this->record['crmid']]);
	}

	protected function toDelete($cal)
	{
		if ($cal['smownerid'] == '') {
			return true;
		}
		$accessibleGroups = $this->user->getAccessibleGroups();
		$db = PearDatabase::getInstance();
		$query = 'SELECT visibility FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid WHERE activityid = ? And vtiger_crmentity.deleted=?';
		$result = $db->pquery($query, [$cal['crmid'], 0]);
		if ($db->num_rows($result) == 0) {
			return true;
		}
		$visibility = $db->query_result_raw($result, 0, 'visibility');
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
			throw new Sabre\DAV\Exception\BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
		}
		if ($componentType === 'VEVENT') {
			$firstOccurence = $component->DTSTART->getDateTime()->getTimeStamp();
			// Finding the last occurence is a bit harder
			if (!isset($component->RRULE)) {
				if (isset($component->DTEND)) {
					$lastOccurence = $component->DTEND->getDateTime()->getTimeStamp();
				} elseif (isset($component->DURATION)) {
					$endDate = clone $component->DTSTART->getDateTime();
					$endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
					$lastOccurence = $endDate->getTimeStamp();
				} elseif (!$component->DTSTART->hasTime()) {
					$endDate = clone $component->DTSTART->getDateTime();
					$endDate->modify('+1 day');
					$lastOccurence = $endDate->getTimeStamp();
				} else {
					$lastOccurence = $firstOccurence;
				}
			} else {
				$it = new Sabre\VObject\RecurrenceIterator($vObject, (string) $component->UID);
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
		return [
			'etag' => md5($calendarData),
			'size' => strlen($calendarData),
			'componentType' => $componentType,
			'firstOccurence' => $firstOccurence,
			'lastOccurence' => $lastOccurence,
			'uid' => $uid,
		];
	}

	function getVTimeZone($vcalendar, $tzid, $from = 0, $to = 0)
	{
		if (!$from)
			$from = time();
		if (!$to)
			$to = $from;

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
			}
			// standard time definition
			else {
				$t_std = $trans['ts'];
				$std = $vcalendar->createComponent('STANDARD');
				$cmp = $std;
			}
			if ($cmp) {
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
}
