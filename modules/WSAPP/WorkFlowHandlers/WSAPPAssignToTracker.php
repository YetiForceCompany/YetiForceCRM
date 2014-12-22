<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
require_once 'include/Webservices/Utils.php';
require_once 'include/events/VTEntityData.inc';
require_once 'data/VTEntityDelta.php';
require_once 'include/Webservices/DataTransform.php';
require_once 'modules/WSAPP/SyncServer.php';

class WSAPPAssignToTracker extends VTEventHandler{
	 function  __construct() {

	}

	function handleEvent($eventName, $entityData) {
		global $current_user;
		$db = PearDatabase::getInstance();
		$moduleName = $entityData->getModuleName();
		
		//Specific to VAS
		if ($moduleName == 'Users') { 
			return; 
		}	 
		//END
		
		$recordId = $entityData->getId();
		$vtEntityDelta = new VTEntityDelta ();
		$newEntityData = $vtEntityDelta->getNewEntity($moduleName,$recordId);
		$recordValues = $newEntityData->getData();
		$isAssignToModified = $this->isAssignToChanged($moduleName,$recordId,$current_user);
		if(!$isAssignToModified){
			return;
		}
		$wsModuleName = $this->getWsModuleName($moduleName);
		if($wsModuleName =="Calendar")
		{
				$wsModuleName = vtws_getCalendarEntityType($recordId);
		}
		$handler = vtws_getModuleHandlerFromName($wsModuleName, $current_user);
		$meta = $handler->getMeta();
		$recordWsValues = DataTransform::sanitizeData($recordValues,$meta);
		$syncServer = new SyncServer();
		$syncServer->markRecordAsDeleteForAllCleints($recordWsValues);
	}

	function isAssignToChanged($moduleName,$recordId,$user){
		$wsModuleName = $this->getWsModuleName($moduleName);
		$handler = vtws_getModuleHandlerFromName($wsModuleName, $user);
		$meta = $handler->getMeta();
		$moduleOwnerFields = $meta->getOwnerFields();
		$assignToChanged = false;
		$vtEntityDelta = new VTEntityDelta ();
		foreach($moduleOwnerFields as $ownerField){
			$assignToChanged = $vtEntityDelta->hasChanged($moduleName, $recordId, $ownerField);
			if($assignToChanged)
				break;
		}
		return $assignToChanged;
	}

	function getWsModuleName($workFlowModuleName){
		//TODO: Handle getting the webservice modulename in a better way
		$wsModuleName = $workFlowModuleName;
		if($workFlowModuleName == "Activity")
			$wsModuleName = "Calendar";
		return $wsModuleName;
	}
}

?>
