<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PBXManager_Server_Model extends Vtiger_Base_Model{

    const tableName = 'vtiger_pbxmanager_gateway';
    
    public static function getCleanInstance(){
        return new self; 
    }
    
    /**
     * Static Function Server Record Model
     * @params <string> gateway name
     * @return PBXManager_Server_Model
	 */
    public static function getInstance(){
        $serverModel = new self();
        $db = PearDatabase::getInstance();
        $query = 'SELECT * FROM '.self::tableName;
        $gatewatResult = $db->pquery($query, array());
        $gatewatResultCount = $db->num_rows($gatewatResult);
        
        if($gatewatResultCount > 0) {
            $rowData = $db->query_result_rowdata($gatewatResult, 0);
            $serverModel->set('gateway',$rowData['gateway']);
            $serverModel->set('id',$rowData['id']);
            $parameters = Zend_Json::decode(decode_html($rowData['parameters']));
            foreach ($parameters as $fieldName => $fieldValue) {
                    $serverModel->set($fieldName,$fieldValue);
            }
            return $serverModel;
        }
        return $serverModel;
    }
    
    public static function checkPermissionForOutgoingCall(){
            Users_Privileges_Model::getCurrentUserPrivilegesModel();
            $permission = Users_Privileges_Model::isPermitted('PBXManager', 'MakeOutgoingCalls');

            $serverModel = PBXManager_Server_Model::getInstance();
            $gateway = $serverModel->get('gateway');
            
            if($permission && $gateway){
                return true;
            }else {
                return false;
            }
    }
    
    public static function generateVtigerSecretKey() {
        return uniqid(rand());
    }
    
    public function getConnector(){
        return new PBXManager_PBXManager_Connector;
    }
}
