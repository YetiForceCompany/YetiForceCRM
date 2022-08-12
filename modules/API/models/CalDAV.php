<?php

/**
 * Api CalDAV Model Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * Cache.
	 *
	 * @var array
	 */
	protected static $cache = [];

	/**
	 * Sync from CRM to DAV.
	 *
	 * @return int
	 */
	public function crm2Dav(): int
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		$queryGenerator = new \App\QueryGenerator('Calendar');
		$queryGenerator->setFields(['id', 'subject', 'subject', 'activitytype', 'date_start', 'due_date', 'time_start', 'time_end', 'activitystatus', 'taskpriority', 'location', 'visibility', 'assigned_user_id', 'allday', 'state', 'createdtime', 'modifiedtime', 'description']);
		$queryGenerator->setCustomColumn(['vtiger_crmentity.deleted']);
		$queryGenerator->permissions = false;
		$query = $queryGenerator->createQuery();
		$query->where(['vtiger_crmentity.deleted' => 0, 'vtiger_activity.dav_status' => 1]);
		$dataReader = $query->createCommand()->query();
		$i = 0;
		while ($row = $dataReader->read()) {
			$this->record = $row;
			$i += $this->davSync();
		}
		$dataReader->close();
		\App\Log::trace(__METHOD__ . ' | End');
		return $i;
	}

	/**
	 * Dav sync.
	 *
	 * @return int
	 */
	public function davSync(): int
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
		return $create + $updates;
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
			$instance->davSaveAttendee($this->record);
		}
		$calendarData = $calendar->serialize();
		$extraData = $instance->getDenormalizedData($calendarData);
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
			$instance->davSaveAttendee($this->record);
		}
		$calendarData = $calendar->serialize();
		$extraData = $instance->getDenormalizedData($calendarData);
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
	 * Sync from DAV to CRM.
	 *
	 * @return int
	 */
	public function dav2Crm(): int
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		$i = 0;
		foreach ($this->davUsers as $user) {
			$this->calendarId = $user->get('calendarsid');
			$this->user = $user;
			$i += $this->recordSync();
		}
		\App\Log::trace(__METHOD__ . ' | End');
		return $i;
	}

	/**
	 * Sync record.
	 *
	 * @return int
	 */
	public function recordSync(): int
	{
		\App\Log::trace('Start', __METHOD__);
		$query = (new \App\Db\Query())->select([
			'dav_calendarobjects.*',
			'vtiger_crmentity.modifiedtime', 'vtiger_crmentity.setype',	'assigned_user_id' => 'vtiger_crmentity.smownerid',
			'vtiger_crmentity.crmid', 'vtiger_crmentity.deleted', 'vtiger_activity.visibility'
		])->from('dav_calendarobjects')
			->leftJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = dav_calendarobjects.crmid')
			->leftJoin('vtiger_activity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')
			->where(['calendarid' => $this->calendarId]);
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
				\App\Integrations\Dav\Calendar::delete($row);
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
		return $create + $updates + $deletes;
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
			$recordModel->set('assigned_user_id', $this->user->get('id'));
			$exclusion = \App\Config::component('Dav', 'CALDAV_EXCLUSION_FROM_DAV');
			if (\is_array($exclusion)) {
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
			$calendar->recordSaveAttendee($recordModel);
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
			$calendar->recordSaveAttendee($recordModel);
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
				$isPermitted = (int) $cal['assigned_user_id'] === $userId || \in_array($userId, \App\Fields\SharedOwner::getById($cal['id']));
				break;
			case 'PLL_OWNER_PERSON_GROUP':
				$shownerIds = \App\Fields\SharedOwner::getById($cal['id']);
				$isPermitted = (int) $cal['assigned_user_id'] === $userId || \in_array($cal['assigned_user_id'], $this->user->get('groups')) || \in_array($userId, $shownerIds) || \count(array_intersect($shownerIds, $this->user->get('groups'))) > 0;
				break;
			case 'PLL_OWNER':
			default:
				$isPermitted = (int) $cal['assigned_user_id'] === $userId;
				break;
		}
		if (!$isPermitted && 'Public' !== $cal['visibility']) {
			return true;
		}
		return false;
	}
}
