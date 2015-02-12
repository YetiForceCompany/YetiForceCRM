<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class ContactsPortalPass {
    private $moduleList = array('Contacts');
    
    function process($data){
		if($data['password'] && $data['password'] != '')
			return $data['password'];
		if($data['record'] && $data['record'] != ''){
			$adb = PearDatabase::getInstance();
			$sql = "SELECT user_password FROM vtiger_portalinfo WHERE id=?";
			$result = $adb->pquery($sql, array( $data['record'] ));
			$password = $adb->query_result($result,0,'user_password');
			return $password;
		}
    }
    
    function getListAllowedModule() {
        return $this->moduleList;
    }
}
