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
require_once 'include/events/VTEntityDelta.php';

class ModTrackerHandler extends VTEventHandler
{

	public function handleEvent($eventName, $data)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();

		if (!is_object($data)) {
			$extendedData = $data;
			$data = $extendedData['entityData'];
		}
		$moduleName = $data->getModuleName();
		$flag = ModTracker::isTrackingEnabledForModule($moduleName);

		if ($flag) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$watchdogTitle = $watchdogMessage = '';
			switch ($eventName) {
				case 'vtiger.entity.aftersave.final':

					$recordId = $data->getId();
					$columnFields = $data->getData();
					$vtEntityDelta = new VTEntityDelta();
					$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);

					$newerEntity = $vtEntityDelta->getNewEntity($moduleName, $recordId);
					$newerColumnFields = $newerEntity->getData();
					$newerColumnFields = array_change_key_case($newerColumnFields, CASE_LOWER);
					$delta = array_change_key_case($delta, CASE_LOWER);
					if (is_array($delta)) {
						$inserted = false;
						foreach ($delta as $fieldName => $values) {
							if (!in_array($fieldName, ['modifiedtime', 'modifiedby'])) {
								if (!$inserted) {
									$checkRecordPresentResult = $adb->pquery('SELECT * FROM vtiger_modtracker_basic WHERE crmid = ?', array($recordId));
									if (!$adb->num_rows($checkRecordPresentResult) && $data->isNew()) {
										$status = ModTracker::$CREATED;
										$watchdogTitle = 'LBL_CREATED';
										$watchdogMessage = '(recordChanges: listOfAllValues)';
									} else {
										$status = ModTracker::$UPDATED;
										$watchdogTitle = 'LBL_UPDATED';
										$watchdogMessage = '(recordChanges: listOfAllChanges)';
									}

									$this->id = $adb->getUniqueId('vtiger_modtracker_basic');
									$adb->insert('vtiger_modtracker_basic', [
										'id' => $this->id,
										'crmid' => $recordId,
										'module' => $moduleName,
										'whodid' => $currentUser->getRealId(),
										'changedon' => $newerColumnFields['modifiedtime'],
										'status' => $status,
										'last_reviewed_users' => '#' . $currentUser->getRealId() . '#'
									]);
									if ($status != ModTracker::$CREATED) {
										ModTracker_Record_Model::unsetReviewed($recordId, $currentUser->getRealId(), $this->id);
									}
									$inserted = true;
								}
								$adb->pquery('INSERT INTO vtiger_modtracker_detail(id,fieldname,prevalue,postvalue) VALUES(?,?,?,?)', Array($this->id, $fieldName, $values['oldValue'], $values['currentValue']));
							}
						}
					}
					$isMyRecord = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE smownerid <> ? && crmid = ?', array($currentUser->getRealId(), $recordId));
					if ($adb->num_rows($isMyRecord) > 0) {
						$adb->pquery("UPDATE vtiger_crmentity SET was_read = 0 WHERE crmid = ?;", array($recordId));
					}

					break;
				case 'vtiger.entity.beforedelete':

					$recordId = $data->getId();
					$columnFields = $data->getData();
					$id = $adb->getUniqueId('vtiger_modtracker_basic');
					$adb->insert('vtiger_modtracker_basic', [
						'id' => $id,
						'crmid' => $recordId,
						'module' => $moduleName,
						'whodid' => $currentUser->getRealId(),
						'changedon' => date('Y-m-d H:i:s', time()),
						'status' => ModTracker::$DELETED,
						'last_reviewed_users' => '#' . $currentUser->getRealId() . '#'
					]);
					ModTracker_Record_Model::unsetReviewed($recordId, $currentUser->getRealId(), $id);
					$isMyRecord = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE smownerid <> ? && crmid = ?', array($currentUser->getRealId(), $recordId));
					if ($adb->num_rows($isMyRecord) > 0) {
						$adb->pquery("UPDATE vtiger_crmentity SET was_read = 0 WHERE crmid = ?;", array($recordId));
					}
					$watchdogTitle = 'LBL_REMOVED';
					$watchdogMessage = '(recordChanges: listOfAllChanges)';

					break;
				case 'vtiger.entity.afterrestore':

					$recordId = $data->getId();
					$columnFields = $data->getData();
					$id = $adb->getUniqueId('vtiger_modtracker_basic');
					$adb->insert('vtiger_modtracker_basic', [
						'id' => $id,
						'crmid' => $recordId,
						'module' => $moduleName,
						'whodid' => $currentUser->getRealId(),
						'changedon' => date('Y-m-d H:i:s', time()),
						'status' => ModTracker::$RESTORED,
						'last_reviewed_users' => '#' . $currentUser->getRealId() . '#'
					]);
					ModTracker_Record_Model::unsetReviewed($recordId, $currentUser->getRealId(), $id);
					$isMyRecord = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE smownerid <> ? && crmid = ?', array($currentUser->getRealId(), $recordId));
					if ($adb->num_rows($isMyRecord) > 0) {
						$adb->pquery("UPDATE vtiger_crmentity SET was_read = 0 WHERE crmid = ?;", array($recordId));
					}
					$watchdogTitle = 'LBL_RESTORED';

					break;
				case 'vtiger.entity.link.after':

					$recordId = $extendedData['destinationRecordId'];
					$moduleName = $extendedData['destinationModule'];
					ModTracker::linkRelation($extendedData['sourceModule'], $extendedData['sourceRecordId'], $extendedData['destinationModule'], $extendedData['destinationRecordId']);
					$watchdogTitle = 'LBL_ADDED';
					if (AppConfig::module('ModTracker', 'WATCHDOG')) {
						$watchdogMessage = '<a href="index.php?module=' . $extendedData['sourceModule'] . '&view=Detail&record=' . $extendedData['sourceRecordId'] . '">' . vtlib\Functions::getCRMRecordLabel($extendedData['sourceRecordId']) . '</a>';
						$watchdogMessage .= ' (translate: [LBL_WITH]) ';
						$watchdogMessage .= '<a href="index.php?module=' . $extendedData['destinationModule'] . '&view=Detail&record=' . $extendedData['destinationRecordId'] . '">(general: RecordLabel)</a>';
					}

					break;
				case 'vtiger.entity.unlink.after':

					$recordId = $extendedData['destinationRecordId'];
					$moduleName = $extendedData['destinationModule'];
					ModTracker::unLinkRelation($extendedData['sourceModule'], $extendedData['sourceRecordId'], $extendedData['destinationModule'], $extendedData['destinationRecordId']);
					$watchdogTitle = 'LBL_REMOVED';
					if (AppConfig::module('ModTracker', 'WATCHDOG')) {
						$watchdogMessage = '<a href="index.php?module=' . $extendedData['sourceModule'] . '&view=Detail&record=' . $extendedData['sourceRecordId'] . '">' . vtlib\Functions::getCRMRecordLabel($extendedData['sourceRecordId']) . '</a>';
						$watchdogMessage .= ' (translate: [LBL_WITH]) ';
						$watchdogMessage .= '<a href="index.php?module=' . $extendedData['destinationModule'] . '&view=Detail&record=' . $extendedData['destinationRecordId'] . '">(general: RecordLabel)</a>';
					}

					break;
				case 'entity.convertlead.after':
					// TODU
					break;
				case 'vtiger.view.detail.before':

					$recordId = $data->getId();
					$adb->insert('vtiger_modtracker_basic', [
						'id' => $adb->getUniqueId('vtiger_modtracker_basic'),
						'crmid' => $recordId,
						'module' => $moduleName,
						'whodid' => $currentUser->getRealId(),
						'changedon' => date('Y-m-d H:i:s', time()),
						'status' => ModTracker::$DISPLAYED
					]);

					break;
			}
			if (AppConfig::module('ModTracker', 'WATCHDOG') && $watchdogTitle != '') {
				$actionsTypes = ModTracker::getAllActionsTypes();
				$watchdogTitle = '(translate: [' . $watchdogTitle . '|||ModTracker]) (general: RecordLabel)';
				if (in_array($watchdogTitle, [0, 2, 3, 7])) {
					$watchdogTitle = '<a href="index.php?module=' . $moduleName . '&view=Detail&record=' . $recordId . '">' . $watchdogTitle . '</a>';
				}
				$watchdogTitle = $currentUser->getName() . ' ' . $watchdogTitle;
				$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordId, $moduleName);
				$users = $watchdog->getWatchingUsers();
				if (!empty($users)) {
					foreach ($users as $userId) {
						$notification = Home_Notification_Model::getInstance();
						$notification->set('record', $recordId);
						$notification->set('moduleName', $moduleName);
						$notification->set('title', $watchdogTitle);
						$notification->set('message', $watchdogMessage);
						$notification->set('type', $watchdog->notificationDefaultType);
						$notification->set('userid', $userId);
						$notification->save();
					}
				}
			}
		}
	}
}
