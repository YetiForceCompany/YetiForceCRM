<?php
require_once 'include/events/VTEventHandler.inc';
class Vtiger_SharingPrivileges_Handler extends VTEventHandler {
	function handleEvent($eventName, $data) {
		global $adb,$shared_owners;
		if ($eventName == 'vtiger.entity.aftersave.final' && $shared_owners == true) {
            $moduleName = $data->getModuleName();
			$recordId = $data->getId();
			if($data->get('inheritsharing') != NULL && $data->get('inheritsharing') !== 0){
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);
				if ( array_key_exists("shownerid",$delta) ){
					$oldValue = Vtiger_Functions::getArrayFromValue( $delta['shownerid']['oldValue'] );
					$currentValue = Vtiger_Functions::getArrayFromValue( $delta['shownerid']['currentValue'] );
					if( count($oldValue) == 0 ){
						$addUser = $currentValue;
						$removeUser = array();
					}else{
						$removeUser = array_diff ($oldValue, $currentValue);
						$addUser = array_diff ($currentValue, $oldValue);
					}
					Users_Privileges_Model::setSharedOwnerRecursively( $recordId , $addUser, $removeUser, $moduleName );
				}
			}
		}
		if ($eventName == 'vtiger.entity.link.after') {
			$destinationModule = array('Products', 'Services');
			if ($entityData['sourceModule'] == 'Potentials' && in_array($entityData['destinationModule'], $destinationModule)) {
				global $adb;
				$result1 = $adb->pquery('SELECT smownerid FROM vtiger_crmentity WHERE crmid = ?;', array($entityData['destinationRecordId']));
				$result2 = $adb->pquery('SELECT smownerid,shownerid FROM vtiger_crmentity WHERE crmid = ?;', array($entityData['sourceRecordId']));
				if ($adb->num_rows($result1) == 1 && $adb->num_rows($result2) == 1) {
					$smownerid = $adb->query_result($result1, 0, 'smownerid');
					$shownerid = $adb->query_result($result2, 0, 'shownerid') . ',' . $smownerid;

					if ($smownerid != $adb->query_result($result2, 0, 'smownerid')) {
						$adb->pquery("UPDATE vtiger_crmentity SET shownerid = ? WHERE crmid = ?;", array(rtrim($shownerid, ','), $entityData['sourceRecordId']));
					}
				}
			}
		}
	}
}