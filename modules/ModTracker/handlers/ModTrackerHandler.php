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
require_once __DIR__ . '/../ModTracker.php';

class ModTracker_ModTrackerHandler_Handler
{
	/**
	 * EntityAfterSave function.
	 *
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
			if ($recordModel->getModule()->isInventory() && ($invData = $recordModel->getInventoryData())) {
				$delta['inventory'] = array_fill_keys(array_keys($invData), []);
			}
			unset($delta['createdtime'], $delta['modifiedtime'], $delta['id'], $delta['newRecord'], $delta['modifiedby']);
			$status = ModTracker::$CREATED;
			$watchdogTitle = 'LBL_CREATED';
			$watchdogMessage = '$(record : ChangesListValues)$';
		} elseif (isset($recordModel->ext['modificationType'], ModTracker::getAllActionsTypes()[$recordModel->ext['modificationType']])) {
			$delta = $recordModel->getChanges();
			$status = $recordModel->ext['modificationType'];
			$watchdogTitle = $status === ModTracker::$TRANSFER_EDIT ? ModTracker_Record_Model::$statusLabel[$status] : '';
			$watchdogMessage = '';
		} else {
			$delta = $recordModel->getChanges();
			$status = ModTracker::$UPDATED;
			$watchdogTitle = 'LBL_UPDATED';
			$watchdogMessage = '$(record : ChangesListValues)$';
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
		if (isset($delta['inventory'])) {
			$inventoryData = [];
			foreach ($delta['inventory'] as $key => $invData) {
				$item = $recordModel->getInventoryItem($key) ?? [];
				$itemId = $invData['id'] ?? $item['id'];
				$inventoryData[$itemId]['item'] = $invData['name'] ?? $item['name'];
				$inventoryData[$itemId]['prevalue'] = $invData;
				$inventoryData[$itemId]['postvalue'] = $invData ? array_intersect_key($item, $invData) : $item;
			}
			$db->createCommand()->insert('u_#__modtracker_inv', ['id' => $id, 'changes' => \App\Json::encode($inventoryData)])->execute();
			unset($delta['inventory']);
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
		if ($insertedData) {
			$db->createCommand()
				->batchInsert('vtiger_modtracker_detail', ['id', 'fieldname', 'prevalue', 'postvalue'], $insertedData)
				->execute();
		}
		$this->addNotification($eventHandler->getModuleName(), $recordId, $watchdogTitle, $watchdogMessage);
	}

	/**
	 * EntityAfterLink handler function.
	 *
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
			$watchdogMessage .= ' $(translate : LBL_WITH)$ ';
			$watchdogMessage .= '<a href="index.php?module=' . $params['destinationModule'] . '&view=Detail&record=' . $params['destinationRecordId'] . '">$(record : RecordLabel)$</a>';
			$this->addNotification($params['destinationModule'], $params['destinationRecordId'], $watchdogTitle, $watchdogMessage);
		}
	}

	/**
	 * EntityAfterUnLink handler function.
	 *
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
			$watchdogMessage .= ' $(translate : LBL_WITH)$ ';
			$watchdogMessage .= '<a href="index.php?module=' . $params['destinationModule'] . '&view=Detail&record=' . $params['destinationRecordId'] . '">$(record : RecordLabel)$</a>';
			$this->addNotification($params['destinationModule'], $params['destinationRecordId'], $watchdogTitle, $watchdogMessage);
		}
	}

	/**
	 * EntityAfterTransferLink handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 *
	 * @return bool
	 */
	public function entityAfterTransferLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		if (!ModTracker::isTrackingEnabledForModule($params['destinationModule'])) {
			return false;
		}
		ModTracker::transferRelation($eventHandler->getModuleName(), $params['sourceRecordId'], $params['destinationModule'], $params['destinationRecordId'], ModTracker::$TRANSFER_LINK);
	}

	/**
	 * @param App\EventHandler $eventHandler
	 *
	 * @return bool
	 */
	public function entityAfterTransferUnLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		if (!ModTracker::isTrackingEnabledForModule($params['destinationModule'])) {
			return false;
		}
		ModTracker::transferRelation($eventHandler->getModuleName(), $params['sourceRecordId'], $params['destinationModule'], $params['destinationRecordId'], ModTracker::$TRANSFER_UNLINK);
	}

	/**
	 * DetailViewBefore handler function.
	 *
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
			'status' => ModTracker::$DISPLAYED,
		])->execute();
	}

	/**
	 * EntityChangeState handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function entityChangeState(App\EventHandler $eventHandler)
	{
		if (!ModTracker::isTrackingEnabledForModule($eventHandler->getModuleName())) {
			return false;
		}
		$recordModel = $eventHandler->getRecordModel();
		$recordId = $recordModel->getId();
		$status = 0;
		if (isset($recordModel->ext['modificationType'], ModTracker::getAllActionsTypes()[$recordModel->ext['modificationType']])) {
			$status = $recordModel->ext['modificationType'];
		} else {
			switch ($recordModel->get('deleted')) {
				case 'Active':
					$status = ModTracker::$ACTIVE;
					break;
				case 'Trash':
					$status = ModTracker::$TRASH;
					break;
				case 'Archived':
					$status = ModTracker::$ARCHIVED;
					break;
				default:
					break;
			}
		}
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_modtracker_basic', [
			'crmid' => $recordId,
			'module' => $eventHandler->getModuleName(),
			'whodid' => \App\User::getCurrentUserRealId(),
			'changedon' => date('Y-m-d H:i:s'),
			'status' => $status,
			'last_reviewed_users' => '#' . \App\User::getCurrentUserRealId() . '#'
		])->execute();
		$id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
		ModTracker_Record_Model::unsetReviewed($recordId, \App\User::getCurrentUserRealId(), $id);
		$isExists = (new \App\Db\Query())->from('vtiger_crmentity')->where(['crmid' => $recordId])->andWhere(['<>', 'smownerid', \App\User::getCurrentUserRealId()])->exists();
		if ($isExists) {
			$db->createCommand()->update('vtiger_crmentity', ['was_read' => 0], ['crmid' => $recordId])->execute();
		}
		$this->addNotification($eventHandler->getModuleName(), $recordId, ModTracker_Record_Model::$statusLabel[$status]);
	}

	/**
	 * Add notification in handler.
	 *
	 * @param string $moduleName
	 * @param int    $recordId
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
				$watchdogTitle = '$(translate : ModTracker|' . $watchdogTitle . ')$ $(record : RecordLabel)$';
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
