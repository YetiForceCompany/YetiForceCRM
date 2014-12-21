<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Vtiger_Systems_Model extends Vtiger_Base_Model{

    const tableName = 'vtiger_systems';

    public function getId() {
        return $this->get('id');
    }

    public function isSmtpAuthEnabled() {
        $smtp_auth_value = $this->get('smtp_auth');
        return ($smtp_auth_value == 'on' || $smtp_auth_value == 1 || $smtp_auth_value == 'true') ? true : false;
    }

    public function save() {
        $db = PearDatabase::getInstance();

        $id = $this->getId();
        $params = array();
        array_push($params, $this->get('server'),$this->get('server_port'),$this->get('server_username'),$this->get('server_password'),$this->get('server_type'),
                   $this->isSmtpAuthEnabled(),$this->get('server_path'),$this->get('from_email_field'));

        if(empty($id)) {
            $id = $db->getUniqueID(self::tableName);
            //To keep id in the beginning
            array_unshift($params, $id);
            $query = 'INSERT INTO '.self::tableName.' VALUES(?,?,?,?,?,?,?,?,?)';
        }else{
            $query = 'UPDATE '.self::tableName.' SET server = ?, server_port= ?, server_username = ?, server_password = ?,
                server_type = ?,  smtp_auth= ?, server_path = ?, from_email_field=? WHERE id = ?';
            $params[] = $id;
        }
        $db->pquery($query,$params);
        return $id;
    }

    public static function getInstanceFromServerType($type,$componentName) {
        $db = PearDatabase::getInstance();
        $query = 'SELECT * FROM '.self::tableName.' WHERE server_type=?';
        $params = array($type);
        $result = $db->pquery($query,$params);
        try{
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', $componentName, 'Settings:Vtiger');
        }catch(Exception $e) {
            $modelClassName = self;
        }
        $instance = new $modelClassName();
        if($db->num_rows($result) > 0 ){
            $rowData = $db->query_result_rowdata($result,0);
            $instance->setData($rowData);
        }
        return $instance;
    }

}