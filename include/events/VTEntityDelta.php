<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ********************************************************************************** */
require_once 'include/events/VTEntityData.inc';

class VTEntityDelta extends VTEventHandler
{

	private static $oldEntity;
	private static $newEntity;
	private static $entityDelta;

	public function __construct()
	{
		
	}

	public function handleEvent($eventName, $entityData)
	{
		$adb = PearDatabase::getInstance();
		if (!is_object($entityData)) {
			$extendedData = $entityData;
			$entityData = $extendedData['entityData'];
		}
		$moduleName = $entityData->getModuleName();
		$recordId = $entityData->getId();
		if ($eventName == 'vtiger.entity.beforesave' || $eventName == 'vtiger.entity.unlink.before') {
			if (!$entityData->isNew()) {
				$entityData = VTEntityData::fromEntityId($adb, $recordId, $moduleName);
				if ($moduleName == 'HelpDesk') {
					$entityData->set('comments', \vtlib\Functions::getTicketComments($recordId));
				}
				self::$oldEntity[$moduleName][$recordId] = $entityData;
			}
		}
		if ($eventName == 'vtiger.entity.aftersave' || $eventName == 'vtiger.entity.unlink.after') {
			$this->fetchEntity($moduleName, $recordId);
			$this->computeDelta($moduleName, $recordId);
		}
	}

	public function fetchEntity($moduleName, $recordId)
	{
		$adb = PearDatabase::getInstance();
		$entityData = VTEntityData::fromEntityId($adb, $recordId, $moduleName);
		if ($moduleName == 'HelpDesk') {
			$entityData->set('comments', \vtlib\Functions::getTicketComments($recordId));
		}
		self::$newEntity[$moduleName][$recordId] = $entityData;
	}

	public function computeDelta($moduleName, $recordId)
	{

		$delta = [];

		$oldData = [];
		if (!empty(self::$oldEntity[$moduleName][$recordId])) {
			$oldEntity = self::$oldEntity[$moduleName][$recordId];
			$oldData = $oldEntity->getData();
		}
		$newEntity = self::$newEntity[$moduleName][$recordId];
		$newData = $newEntity->getData();
		/** Detect field value changes * */
		foreach ($newData as $fieldName => $fieldValue) {
			$isModified = false;
			if (empty($oldData[$fieldName])) {
				if (!empty($newData[$fieldName])) {
					$isModified = true;
				}
			} elseif ($oldData[$fieldName] != $newData[$fieldName]) {
				$isModified = true;
			}
			if ($isModified) {
				$delta[$fieldName] = array('oldValue' => isset($oldData[$fieldName]) ? $oldData[$fieldName] : '',
					'currentValue' => $newData[$fieldName]);
			}
		}
		self::$entityDelta[$moduleName][$recordId] = $delta;
	}

	public function getEntityDelta($moduleName, $recordId, $forceFetch = false)
	{
		if ($forceFetch) {
			$this->fetchEntity($moduleName, $recordId);
			$this->computeDelta($moduleName, $recordId);
		}
		return self::$entityDelta[$moduleName][$recordId];
	}

	public function getOldValue($moduleName, $recordId, $fieldName)
	{
		$entityDelta = self::$entityDelta[$moduleName][$recordId];
		return $entityDelta[$fieldName]['oldValue'];
	}

	public function getCurrentValue($moduleName, $recordId, $fieldName)
	{
		$entityDelta = self::$entityDelta[$moduleName][$recordId];
		return $entityDelta[$fieldName]['currentValue'];
	}

	public function getOldEntity($moduleName, $recordId)
	{
		return self::$oldEntity[$moduleName][$recordId];
	}

	public function getNewEntity($moduleName, $recordId)
	{
		return self::$newEntity[$moduleName][$recordId];
	}

	public function hasChanged($moduleName, $recordId, $fieldName, $fieldValue = NULL)
	{
		if (empty(self::$oldEntity[$moduleName][$recordId])) {
			return false;
		}
		$fieldDelta = self::$entityDelta[$moduleName][$recordId][$fieldName];
		$result = $fieldDelta['oldValue'] != $fieldDelta['currentValue'];
		if ($fieldValue !== NULL) {
			$result = $result && ($fieldDelta['currentValue'] === $fieldValue);
		}
		return $result;
	}
}
