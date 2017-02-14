<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
require_once dirname(__FILE__) . '/../ModTracker.php';

class ModTracker_ModTrackerHandler_Handler
{

	/**
	 * EntityAfterSave function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		if (!ModTracker::isTrackingEnabledForModule($eventHandler->getModuleName())) {
			return false;
		}
		$recordModel = $eventHandler->getRecordModel();
		if ($recordModel->isNew()) {
			$delta = $recordModel->getData();
			unset($delta['createdtime'], $delta['modifiedtime'], $delta['id'], $delta['newRecord'], $delta['modifiedby']);
			$status = ModTracker::$CREATED;
			$watchdogTitle = 'LBL_CREATED';
			$watchdogMessage = '(recordChanges: listOfAllValues)';
		} else {
			$delta = $recordModel->getPreviousValue();
			$status = ModTracker::$UPDATED;
			$watchdogTitle = 'LBL_UPDATED';
			$watchdogMessage = '(recordChanges: listOfAllChanges)';
		}
		if (empty($delta)) {
			return false;
		}
		$recordId = $recordModel->getId();
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_modtracker_basic', [
			'crmid' => $recordId,
			'module' => $eventHandler->getModuleName(),
			'whodid' => App\User::getCurrentUserRealId(),
			'changedon' => $recordModel->get('modifiedtime'),
			'status' => $status,
			'last_reviewed_users' => '#' . App\User::getCurrentUserRealId() . '#'
		])->execute();
		$id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
		if (!$recordModel->isNew()) {
			ModTracker_Record_Model::unsetReviewed($recordId, App\User::getCurrentUserRealId(), $id);
		}
		$insertedData = [];
		foreach ($delta as $fieldName => &$preValue) {
			$newValue = $recordModel->get($fieldName);
			if (empty($preValue) && empty($newValue)) {
				continue;
			}
			if (is_object($newValue)) {
				throw new App\Exceptions\AppException("Incorrect data type in $fieldName: Value can not be the object of " . get_class($newValue));
			}
			if (is_array($newValue)) {
				$newValue = implode(',', $newValue);
			}
			$insertedData[] = [$id, $fieldName, $preValue, $newValue];
		}
		$db->createCommand()
			->batchInsert('vtiger_modtracker_detail', ['id', 'fieldname', 'prevalue', 'postvalue'], $insertedData)
			->execute();
		if (!$recordModel->isNew()) {
			$isExists = (new \App\Db\Query())->from('vtiger_crmentity')->where(['crmid' => $recordId])->andWhere(['<>', 'smownerid', App\User::getCurrentUserRealId()])->exists();
			if ($isExists) {
				$db->createCommand()->update('vtiger_crmentity', ['was_read' => 0], ['crmid' => $recordId])->execute();
			}
		}
		$this->addNotification($eventHandler->getModuleName(), $recordId, $watchdogTitle, $watchdogMessage);
	}

	/**
	 * EntityAfterLink handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		if (!ModTracker::isTrackingEnabledForModule($params['destinationModule'])) {
			return false;
		}
		ModTracker::linkRelation($params['sourceModule'], $params['sourceRecordId'], $params['destinationModule'], $params['destinationRecordId']);
		if (AppConfig::module('ModTracker', 'WATCHDOG')) {
			$watchdogTitle = 'LBL_ADDED';
			$watchdogMessage = '<a href="index.php?module=' . $params['sourceModule'] . '&view=Detail&record=' . $params['sourceRecordId'] . '">' . vtlib\Functions::getCRMRecordLabel($params['sourceRecordId']) . '</a>';
			$watchdogMessage .= ' (translate: [LBL_WITH]) ';
			$watchdogMessage .= '<a href="index.php?module=' . $params['destinationModule'] . '&view=Detail&record=' . $params['destinationRecordId'] . '">(general: RecordLabel)</a>';
			$this->addNotification($params['destinationModule'], $params['destinationRecordId'], $watchdogTitle, $watchdogMessage);
		}
	}

	/**
	 * EntityAfterUnLink handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterUnLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		if (!ModTracker::isTrackingEnabledForModule($params['destinationModule'])) {
			return false;
		}
		ModTracker::unLinkRelation($params['sourceModule'], $params['sourceRecordId'], $params['destinationModule'], $params['destinationRecordId']);
		if (AppConfig::module('ModTracker', 'WATCHDOG')) {
			$watchdogTitle = 'LBL_REMOVED';
			$watchdogMessage = '<a href="index.php?module=' . $params['sourceModule'] . '&view=Detail&record=' . $params['sourceRecordId'] . '">' . vtlib\Functions::getCRMRecordLabel($params['sourceRecordId']) . '</a>';
			$watchdogMessage .= ' (translate: [LBL_WITH]) ';
			$watchdogMessage .= '<a href="index.php?module=' . $params['destinationModule'] . '&view=Detail&record=' . $params['destinationRecordId'] . '">(general: RecordLabel)</a>';
			$this->addNotification($params['destinationModule'], $params['destinationRecordId'], $watchdogTitle, $watchdogMessage);
		}
	}

	/**
	 * DetailViewBefore handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function detailViewBefore(App\EventHandler $eventHandler)
	{
		if (!ModTracker::isTrackingEnabledForModule($eventHandler->getModuleName())) {
			return false;
		}
		$recordModel = $eventHandler->getRecordModel();
		\App\Db::getInstance()->createCommand()->insert('vtiger_modtracker_basic', [
			'crmid' => $recordModel->getId(),
			'module' => $eventHandler->getModuleName(),
			'whodid' => \App\User::getCurrentUserRealId(),
			'changedon' => date('Y-m-d H:i:s'),
			'status' => ModTracker::$DISPLAYED
		])->execute();
	}

	/**
	 * EntityAfterRestore handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterRestore(App\EventHandler $eventHandler)
	{
		$recordId = $eventHandler->getRecordModel()->getId();
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_modtracker_basic', [
			'crmid' => $recordId,
			'module' => $eventHandler->getModuleName(),
			'whodid' => \App\User::getCurrentUserRealId(),
			'changedon' => date('Y-m-d H:i:s'),
			'status' => ModTracker::$RESTORED,
			'last_reviewed_users' => '#' . \App\User::getCurrentUserRealId() . '#'
		])->execute();
		$id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
		ModTracker_Record_Model::unsetReviewed($recordId, \App\User::getCurrentUserRealId(), $id);
		$isExists = (new \App\Db\Query())->from('vtiger_crmentity')->where(['crmid' => $recordId])->andWhere(['<>', 'smownerid', \App\User::getCurrentUserRealId()])->exists();
		if ($isExists) {
			$db->createCommand()->update('vtiger_crmentity', ['was_read' => 0], ['crmid' => $recordId])->execute();
		}
		$this->addNotification($eventHandler->getModuleName(), $recordId, 'LBL_RESTORED');
	}

	/**
	 * EntityBeforeDelete handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityBeforeDelete(App\EventHandler $eventHandler)
	{
		$recordId = $eventHandler->getRecordModel()->getId();
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_modtracker_basic', [
			'crmid' => $recordId,
			'module' => $eventHandler->getModuleName(),
			'whodid' => \App\User::getCurrentUserRealId(),
			'changedon' => date('Y-m-d H:i:s'),
			'status' => ModTracker::$DELETED,
			'last_reviewed_users' => '#' . \App\User::getCurrentUserRealId() . '#'
		])->execute();
		$id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
		ModTracker_Record_Model::unsetReviewed($recordId, \App\User::getCurrentUserRealId(), $id);
		$isExists = (new \App\Db\Query())->from('vtiger_crmentity')->where(['crmid' => $recordId])->andWhere(['<>', 'smownerid', \App\User::getCurrentUserRealId()])->exists();
		if ($isExists) {
			$db->createCommand()->update('vtiger_crmentity', ['was_read' => 0], ['crmid' => $recordId])->execute();
		}
		$this->addNotification($eventHandler->getModuleName(), $recordId, 'LBL_REMOVED');
	}

	/**
	 * Add notification in handler
	 * @param string $moduleName
	 * @param int $recordId
	 * @param string $watchdogTitle
	 * @param string $watchdogMessage
	 */
	public function addNotification($moduleName, $recordId, $watchdogTitle, $watchdogMessage = '')
	{
		if ($watchdogTitle) {
			$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordId, $moduleName);
			$users = $watchdog->getWatchingUsers([\App\User::getCurrentUserRealId()]);
			if (!empty($users)) {
				$currentUser = \App\User::getCurrentUserModel();
				$watchdogTitle = '(translate: [' . $watchdogTitle . '|||ModTracker]) (general: RecordLabel)';
				$watchdogTitle = $currentUser->getName() . ' ' . $watchdogTitle;
				$relatedField = \App\ModuleHierarchy::getMappingRelatedField($moduleName);
				if ($relatedField) {
					$notification = Vtiger_Record_Model::getCleanInstance('Notification');
					$notification->set('shownerid', $users);
					$notification->set($relatedField, $recordId);
					$notification->set('title', $watchdogTitle);
					$notification->set('description', $watchdogMessage);
					$notification->set('notification_type', $watchdog->noticeDefaultType);
					$notification->set('notification_status', 'PLL_UNREAD');
					$notification->setHandlerExceptions(['disableHandlerByName' => ['ModTracker_ModTrackerHandler_Handler']]);
					$notification->save();
				}
			}
		}
	}
}
