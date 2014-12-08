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
	}
}