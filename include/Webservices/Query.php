<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

	require_once("include/Webservices/QueryParser.php");
	
	function vtws_query($q,$user){
		
		static $vtws_query_cache = array();	
		
		global $log,$adb;

		// Cache the instance for re-use		
		$moduleRegex = "/[fF][rR][Oo][Mm]\s+([^\s;]+)/";
		$moduleName = '';
		if(preg_match($moduleRegex, $q, $m)) $moduleName = trim($m[1]);
		
		if(!isset($vtws_create_cache[$moduleName]['webserviceobject'])) {
			$webserviceObject = VtigerWebserviceObject::fromQuery($adb,$q);
			$vtws_query_cache[$moduleName]['webserviceobject'] = $webserviceObject;
		} else {
			$webserviceObject = $vtws_query_cache[$moduleName]['webserviceobject'];
		}
		// END
		
		$handlerPath = $webserviceObject->getHandlerPath();
		$handlerClass = $webserviceObject->getHandlerClass();
		
		require_once $handlerPath;
		
		// Cache the instance for re-use
		if(!isset($vtws_query_cache[$moduleName]['handler'])) {
			$handler = new $handlerClass($webserviceObject,$user,$adb,$log);
			$vtws_query_cache[$moduleName]['handler'] = $handler;
		} else {
			$handler = $vtws_query_cache[$moduleName]['handler'];
		}
		// END	

		// Cache the instance for re-use
		if(!isset($vtws_query_cache[$moduleName]['meta'])) {
			$meta = $handler->getMeta();
			$vtws_query_cache[$moduleName]['meta'] = $meta;
		} else {
			$meta = $vtws_query_cache[$moduleName]['meta'];
		}
		// END
		
		$types = vtws_listtypes(null, $user);
		if(!in_array($webserviceObject->getEntityName(),$types['types'])){
			throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED,"Permission to perform the operation is denied");
		}
		
		if(!$meta->hasReadAccess()){
			throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED,"Permission to read is denied");
		}
		
		$result = $handler->query($q);
		VTWS_PreserveGlobal::flush();
		return $result;
	}
	
?>