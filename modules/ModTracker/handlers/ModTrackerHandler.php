<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/
require_once dirname(__FILE__) .'/../ModTracker.php';
require_once 'include/events/VTEntityDelta.php';

class ModTrackerHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$log = vglobal('log');
		$current_module = vglobal('current_module');
		if ($eventName == 'vtiger.entity.link.after' || $eventName == 'vtiger.entity.unlink.after') {
			$moduleName = $data['sourceModule'];
		}else{
			$moduleName = $data->getModuleName();
		}

		$flag = ModTracker::isTrackingEnabledForModule($moduleName);

		if ($flag) {
			if ($eventName == 'vtiger.entity.aftersave.final') {
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
						if ($fieldName != 'modifiedtime') {
							if (!$inserted) {
								$checkRecordPresentResult = $adb->pquery('SELECT * FROM vtiger_modtracker_basic WHERE crmid = ?', array($recordId));
								if (!$adb->num_rows($checkRecordPresentResult) && $data->isNew()) {
									$status = ModTracker::$CREATED;
								} else {
									$status = ModTracker::$UPDATED;
								}
								$this->id = $adb->getUniqueId('vtiger_modtracker_basic');
								$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
                                            VALUES(?,?,?,?,?,?)', Array($this->id, $recordId, $moduleName,
									$current_user->id, $newerColumnFields['modifiedtime'], $status));
								$inserted = true;
							}
							$adb->pquery('INSERT INTO vtiger_modtracker_detail(id,fieldname,prevalue,postvalue) VALUES(?,?,?,?)', Array($this->id, $fieldName, $values['oldValue'], $values['currentValue']));
						}
					}
				}
				$isMyRecord = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE smownerid <> ? AND crmid = ?', array($current_user->id, $recordId));
				if ($adb->num_rows($isMyRecord) > 0)
					$adb->pquery("UPDATE vtiger_crmentity SET was_read = 0 WHERE crmid = ?;", array($recordId));
			}

			if ($eventName == 'vtiger.entity.beforedelete') {
				$recordId = $data->getId();
				$columnFields = $data->getData();
				$id = $adb->getUniqueId('vtiger_modtracker_basic');
				$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
                    VALUES(?,?,?,?,?,?)', Array($id, $recordId, $moduleName, $current_user->id, date('Y-m-d H:i:s', time()), ModTracker::$DELETED));
				$isMyRecord = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE smownerid <> ? AND crmid = ?', array($current_user->id, $recordId));
				if ($adb->num_rows($isMyRecord) > 0)
					$adb->pquery("UPDATE vtiger_crmentity SET was_read = 0 WHERE crmid = ?;", array($recordId));
			}

			if ($eventName == 'vtiger.entity.afterrestore') {
				$recordId = $data->getId();
				$columnFields = $data->getData();
				$id = $adb->getUniqueId('vtiger_modtracker_basic');
				$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
                    VALUES(?,?,?,?,?,?)', Array($id, $recordId, $moduleName, $current_user->id, date('Y-m-d H:i:s', time()), ModTracker::$RESTORED));
				$isMyRecord = $adb->pquery('SELECT crmid FROM vtiger_crmentity WHERE smownerid <> ? AND crmid = ?', array($current_user->id, $recordId));
				if ($adb->num_rows($isMyRecord) > 0)
					$adb->pquery("UPDATE vtiger_crmentity SET was_read = 0 WHERE crmid = ?;", array($recordId));
			}
			
			if ($eventName == 'vtiger.entity.link.after') {
				ModTracker::linkRelation($data['sourceModule'], $data['sourceRecordId'], $data['destinationModule'], $data['destinationRecordId']);
			}
			if ($eventName == 'vtiger.entity.unlink.after') {
				ModTracker::unLinkRelation($data['sourceModule'], $data['sourceRecordId'], $data['destinationModule'], $data['destinationRecordId']);
			}
		}
	}

}
