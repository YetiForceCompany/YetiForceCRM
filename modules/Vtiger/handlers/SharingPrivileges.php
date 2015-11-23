<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
*************************************************************************************************************************************/
require_once 'include/events/VTEventHandler.inc';
class Vtiger_SharingPrivileges_Handler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $adb, $shared_owners;
		if ($eventName == 'vtiger.entity.aftersave.final' && $shared_owners == true) {
			$moduleName = $entityData->getModuleName();
			$recordId = $entityData->getId();
			$vtEntityDelta = new VTEntityDelta();
			$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);

			if(array_key_exists("assigned_user_id", $delta)){
				$usersUpadated = TRUE;
				$oldValue = Vtiger_Functions::getArrayFromValue($delta['assigned_user_id']['oldValue']);
				$currentValue = Vtiger_Functions::getArrayFromValue($delta['assigned_user_id']['currentValue']);
				$addUsers = $currentValue;
				$removeUser = array_diff($oldValue, $currentValue);
				Users_Privileges_Model::setSharedOwnerRecursively($recordId, $addUsers, $removeUser, $moduleName);
			}
		}

		if ($eventName == 'vtiger.entity.link.after' && $shared_owners == true && Vtiger_Processes_Model::getConfig('sales', 'popup', 'update_shared_permissions') == 'true') {
			$destinationModule = array('Products', 'Services');
			if ($entityData['sourceModule'] == 'Potentials' && in_array($entityData['destinationModule'], $destinationModule)) {
				$adb = PearDatabase::getInstance();
				$result1 = $adb->pquery('SELECT smownerid, shownerid FROM vtiger_crmentity WHERE crmid = ?;', array($entityData['destinationRecordId']));
				$result2 = $adb->pquery('SELECT shownerid FROM vtiger_crmentity WHERE crmid = ?;', array($entityData['sourceRecordId']));
				if ($adb->num_rows($result1) == 1 && $adb->num_rows($result2) == 1) {
					$smownerid = $adb->query_result($result1, 0, 'smownerid') . ',' . $adb->query_result($result1, 0, 'shownerid');
					$shownerid = $adb->query_result($result2, 0, 'shownerid') . ',' . trim($smownerid, ',');

					$adb->pquery("UPDATE vtiger_crmentity SET shownerid = ? WHERE crmid = ?;", [trim($shownerid, ','), $entityData['sourceRecordId']]);
				}
			}
		}
	}

}

