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
	}
}
