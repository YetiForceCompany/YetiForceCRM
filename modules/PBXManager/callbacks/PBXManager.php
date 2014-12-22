<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
chdir(dirname(__FILE__) . '/../../../');
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';
vimport('includes.http.Request');

class PBXManager_PBXManager_Callbacks {
    
    function validateRequest($vtigersecretkey,$request) {
        if($vtigersecretkey == $request->get('vtigersignature')){
            return true;
        }
        return false;
    }

    function process($request){
	$pbxmanagerController = new PBXManager_PBXManager_Controller();
        $connector = $pbxmanagerController->getConnector();
        if($this->validateRequest($connector->getVtigerSecretKey(),$request)) {
            $pbxmanagerController->process($request);
        }else {
            $response = $connector->getXmlResponse();
            echo $response;
        }
    }
}
$pbxmanager = new PBXManager_PBXManager_Callbacks();
$pbxmanager->process(new Vtiger_Request($_REQUEST));
?>