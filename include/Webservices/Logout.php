<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

function vtws_logout($sessionId,$user){
	$sessionManager = new SessionManager();
	$sid = $sessionManager->startSession($sessionId);
	
	if(!isset($sessionId) || !$sessionManager->isValid()){
		return $sessionManager->getError();
	}

	$sessionManager->destroy();
//	$sessionManager->setExpire(1);
	return array("message"=>"successfull");

}
?>
