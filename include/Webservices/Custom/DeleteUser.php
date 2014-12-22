<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/
 require_once("include/events/include.inc");
/**
 * @author MAK
 */

function vtws_deleteUser($id, $newOwnerId,$user){
		global $log,$adb;
		$webserviceObject = VtigerWebserviceObject::fromId($adb,$id);
		$handlerPath = $webserviceObject->getHandlerPath();
		$handlerClass = $webserviceObject->getHandlerClass();

		require_once $handlerPath;

		$handler = new $handlerClass($webserviceObject,$user,$adb,$log);
		$meta = $handler->getMeta();
		$entityName = $meta->getObjectEntityName($id);

		$types = vtws_listtypes(null, $user);
		if(!in_array($entityName,$types['types'])){
			throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED,
					"Permission to perform the operation is denied, EntityName = ".$entityName);
		}

		if($entityName !== $webserviceObject->getEntityName()){
			throw new WebServiceException(WebServiceErrorCode::$INVALIDID,
					"Id specified is incorrect");
		}

		if(!$meta->hasPermission(EntityMeta::$DELETE,$id)){
			throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED,
					"Permission to read given object is denied");
		}

		$idComponents = vtws_getIdComponents($id);
		if(!$meta->exists($idComponents[1])){
			throw new WebServiceException(WebServiceErrorCode::$RECORDNOTFOUND,
					"Record you are trying to access is not found, idComponent = ".$idComponents);
		}

		if($meta->hasWriteAccess()!==true){
			throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED,
					"Permission to write is denied");
		}

		$newIdComponents = vtws_getIdComponents($newOwnerId);
		if(empty($newIdComponents[1])) {
			//force the default user to be the default admin user.
			$newIdComponents[1] = 1;
		}

		$userObj = new Users();
		$userObj->transformOwnerShipAndDelete($idComponents[1], $newIdComponents[1]);		

		VTWS_PreserveGlobal::flush();
		return  array("status"=>"successful");
	}

?>
