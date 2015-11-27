<?php

/**
 * Sharing privileges handler
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'include/events/VTEventHandler.inc';

class Vtiger_SharingPrivileges_Handler extends VTEventHandler
{

	function handleEvent($eventName, $entityData)
	{
		if ($eventName == 'vtiger.entity.aftersave.final' && vglobal('shared_owners') == true) {
			$moduleName = $entityData->getModuleName();
			$recordId = $entityData->getId();
			$vtEntityDelta = new VTEntityDelta();
			$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);

			if (array_key_exists('assigned_user_id', $delta)) {
				$usersUpadated = TRUE;
				$oldValue = Vtiger_Functions::getArrayFromValue($delta['assigned_user_id']['oldValue']);
				$currentValue = Vtiger_Functions::getArrayFromValue($delta['assigned_user_id']['currentValue']);
				$addUsers = $currentValue;
				$removeUser = array_diff($oldValue, $currentValue);
				Users_Privileges_Model::setSharedOwnerRecursively($recordId, $addUsers, $removeUser, $moduleName);
			}
		}

		if ($eventName == 'vtiger.entity.link.after' && vglobal('shared_owners') == true && Vtiger_Processes_Model::getConfig('sales', 'popup', 'update_shared_permissions') == 'true') {
			$destinationModule = ['Products', 'Services'];
			if ($entityData['sourceModule'] == 'Potentials' && in_array($entityData['destinationModule'], $destinationModule)) {
				$db = PearDatabase::getInstance();
				$sourceRecordId = &$entityData['sourceRecordId'];
				$destinationRecordId = &$entityData['destinationRecordId'];

				$recordMetaData = Vtiger_Functions::getCRMRecordMetadata($sourceRecordId);
				$shownerIds = Vtiger_SharedOwner_UIType::getSharedOwners($sourceRecordId, $entityData['sourceModule']);
				$shownerIds[] = $recordMetaData['smownerid'];
				$shownerIds = array_unique($shownerIds);
				
				$usersExist = [];
				$shownersTable = Vtiger_SharedOwner_UIType::getShownerTable($entityData['destinationModule']);
				$result = $db->pquery('SELECT crmid, userid FROM ' . $shownersTable . ' WHERE userid IN(' . implode(',', $shownerIds) . ') AND crmid = ?', [$destinationRecordId]);
				while ($row = $db->getRow($result)) {
					$usersExist[$row['crmid']][$row['userid']] = true;
				}
				foreach ($shownerIds as $userId) {
					if (!isset($usersExist[$destinationRecordId][$userId])) {
						$db->insert($shownersTable, [
							'crmid' => $destinationRecordId,
							'userid' => $userId,
						]);
					}
				}
			}
		}
	}
}
