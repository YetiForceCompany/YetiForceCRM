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
class Settings_Mobile_Module_Model extends Settings_Vtiger_Module_Model {
	public $serviceDir = 'api/mobile_services';
	
	public function getAllMobileKeys() {
		global $adb;
		$result = $adb->pquery( "SELECT yetiforce_mobile_keys.*, vtiger_users.user_name, vtiger_users.first_name, vtiger_users.last_name, vtiger_users.id AS userid FROM yetiforce_mobile_keys INNER JOIN vtiger_users ON vtiger_users.id = yetiforce_mobile_keys.user WHERE vtiger_users.status = ?;", array( 'Active' ), true );
        $rows = $adb->num_rows($result);
		$keys = Array();
        for($i=0; $i<$rows; $i++){
			$row = $adb->query_result_rowdata($result, $i);
			$keys[ $row['id'] ] = $row;
        }
		return $keys;
	}
	
	public function getAllService() {
		$serices = Array();
		$dir = new DirectoryIterator($this->serviceDir);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
				$tmp = explode('.', $fileinfo->getFilename());
				$serices[$tmp[0]] = 'LBL_MOBILE_'.strtoupper($tmp[0]);
            }
        }
		return $serices;
	}
	
	public function addKey($params){
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery( "SELECT id FROM yetiforce_mobile_keys WHERE user = ? AND service = ?;", array( $params['user'] , $params['service'] ), true );
        $rows = $adb->num_rows($result);
		if($rows != 0){
			return 1;
		}
		$keyLength = 10;
		$key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $keyLength);
		$result = $adb->pquery('INSERT INTO yetiforce_mobile_keys (`user`, `service`, `key`) VALUES (?, ?, ?);', array( $params['user'] , $params['service'], $key ));
		if(!$result)
			return 0;
		return $key;
	}
	
	public function deleteKey($params){
		$adb = PearDatabase::getInstance();
		$adb->pquery('DELETE FROM yetiforce_mobile_keys WHERE user = ? AND service = ?;', array( $params['user'] , $params['service'] ));
	}
}
