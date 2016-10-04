<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once 'include/events/VTBatchEventTrigger.inc';
require_once("include/events/SqlResultIterator.inc");

class VTEventTrigger
{
	/* EventTrigger cache management */

	private static $cache = [];
	private static $cacheLookupType = '';
	private static $mandatoryEventClass = array('VTEntityDelta', 'ModTrackerHandler', 'PBXManagerBatchHandler');

	const CACHE_LOOKUP_TYPE_ALL = 'ALL';

	static function initCache($name = false, $force = false)
	{
		$adb = PearDatabase::getInstance();

		if ($name) {
			$names = $name;
			if (!is_array($names))
				$names = array($name);

			$evtinfos = self::getActiveEventInfos($names);

			foreach ($evtinfos as $k => $v) {
				if (!self::isCached($k) || $force) {
					self::$cache[$forname] = $evtinfos;
				}
			}
		} else {
			if (!self::isCached() || $force) {
				self::$cache = self::getActiveEventInfos($name);
				self::$cacheLookupType = self::CACHE_LOOKUP_TYPE_ALL;
			}
		}
	}

	static function isCached($name = false)
	{
		if ($name === false) {
			if (self::$cacheLookupType == self::CACHE_LOOKUP_TYPE_ALL) {
				// Was init cache done for ALL earlier?
				return true;
			}
		} else {
			return isset(self::$cache[$name]);
		}
		return false;
	}

	static function clearCache($name = false)
	{
		if ($name === false) {
			self::$cache = [];
			self::$cacheLookupType = '';
		} else if (self::isCached($name)) {
			unset(self::$cache[$name]);
		}
	}

	static function lookupCache($name)
	{
		if (self::isCached($name)) {
			return self::$cache[$name];
		} else if (self::$cacheLookupType == self::CACHE_LOOKUP_TYPE_ALL) {
			return [];
		}
		return false;
	}

	static function getActiveEventInfos($name = false)
	{
		$params = [];
		$query = 'SELECT * FROM vtiger_eventhandlers WHERE is_active=1';
		if ($name !== false) {
			if (is_array($name)) {
				$query .= ' AND event_name IN (' . generateQuestionMarks($name) . ')';
			} else {
				$query .= ' AND event_name = ?';
			}
			$params[] = $name;
		}
		if (CRMEntity::isBulkSaveMode()) {
			$query .= ' AND handler_class IN (' . generateQuestionMarks(self::$mandatoryEventClass) . ')';
			array_push($params, self::$mandatoryEventClass);
		}

		$rows = [];
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		while ($row = $db->getRow($result)) {
			$rows[$row['event_name']][] = [
				'condition' => $row['cond'],
				'handler_class' => $row['handler_class'],
				'handler_path' => $row['handler_path'],
				'dependent_on' => $row['dependent_on']
			];
		}

		if ($name)
			return $rows[$name];
		else
			return $rows;
	}

	/** END * */
	function __construct($name)
	{
		$this->name = $name;
	}

	function trigger($data)
	{
		$eventInfos = self::lookupCache($this->name);
		if ($eventInfos === false) {
			$eventInfos = self::getActiveEventInfos($this->name);
		}

		$completedEvents = [];
		if ($eventInfos) {
			while (count($eventInfos) > count($completedEvents)) {
				$handlerCounter = 0; // Tracks the number of handlers triggered for the current iteration.
				foreach ($eventInfos as $eventInfo) {
					$condition = new VTEventCondition($eventInfo['condition']);
					if ($condition->test($data)) {
						$handler_class = $eventInfo['handler_class'];
						if (in_array($handler_class, $completedEvents)) {
							continue;
						}

						$dependentEventsNotCompleted = false;
						$dependentOn = $eventInfo['dependent_on'];
						$dependentEvents = \includes\utils\Json::decode($dependentOn);
						foreach ($dependentEvents as $eventHandlerClass) {
							if (!in_array($eventHandlerClass, $completedEvents)) {
								$dependentEventsNotCompleted = true;
							}
						}
						if ($dependentEventsNotCompleted)
							continue;

						require_once($eventInfo['handler_path']);
						$handler = new $handler_class();

						$handler->handleEvent($this->name, $data);
						$completedEvents[] = $handler_class;
						$handlerCounter++;
					} else {
						// Mark the event-handler as finished - without actually invoking.
						$completedEvents[] = $eventInfo['handler_class'];
					}
				}
				if ($handlerCounter == 0 && count($eventInfos) > count($completedEvents)) {
					$uncompletedEvents = [];
					foreach ($eventInfos as $eventInfo) {
						if (!in_array($eventInfo['handler_class'], $completedEvents)) {
							$uncompletedEvents[] = $eventInfo['handler_class'];
						}
					}
					throw new Exception('Deadlock occured for events: ' . implode(' , ', $uncompletedEvents));
				}
			}
		}
	}

	public static function getInstance($triggerName)
	{
		if (stripos($triggerName, 'batch')) {
			return new VTBatchEventTrigger($triggerName);
		}
		return new self($triggerName);
	}
}
