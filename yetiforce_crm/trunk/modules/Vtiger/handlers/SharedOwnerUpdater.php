<?php
require_once 'include/events/VTEventHandler.inc';
class Vtiger_SharedOwnerUpdater_Handler extends VTEventHandler {
	function handleEvent($eventName, $data) {
		if ($eventName == 'vtiger.entity.aftersave.final') {
            $module = $data->getModuleName();
            if($module != "Users"){
				Users_Privileges_Model::setSharedOwner( $data->get('shownerid'), $data->getId());
            }
		}
	}
}