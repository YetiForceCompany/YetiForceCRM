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
class Settings_Dav_Module_Model extends Settings_Vtiger_Module_Model {
	public $defaultName = 'YetiForceCRM';
	
	public function getAllKeys() {
		global $adb;
		
		$result = $adb->query( 'SELECT dav_users.*, dav_principals.email, dav_principals.displayname, vtiger_users.status, vtiger_users.id AS userid, vtiger_users.user_name FROM dav_users INNER JOIN vtiger_users ON vtiger_users.id = dav_users.userid LEFT JOIN dav_principals ON dav_principals.userid = dav_users.userid' );
        $rows = $adb->num_rows($result);
		$keys = Array();
        for($i=0; $i<$rows; $i++){
			$row = $adb->raw_query_result_rowdata($result, $i);
			$keys[ $row['id'] ] = $row;
        }
		return $keys;
	}
	
	public function addKey($params){
		global $adb;
		$userID = $params['user'];
		$result = $adb->pquery("SELECT id FROM dav_users WHERE userid = ?;", array($userID), true);
		$rows = $adb->num_rows($result);
		if ($rows != 0) {
			return 1;
		}
		$keyLength = 4;
		$key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $keyLength);
		$userModel = Users_Record_Model::getInstanceById($userID, 'Users');
		$digesta1 = md5($userModel->get('user_name') . ':YetiDAV:' . $key);
		$result = $adb->pquery('INSERT INTO dav_users (`username`, `digesta1`, `key`, `userid`) VALUES (?, ?, ?, ?);', 
			array($userModel->get('user_name'), $digesta1, $key, $userID));
		if (!$result)
			return 0;
		$displayname = $userModel->getName();
		$result = $adb->pquery('INSERT INTO dav_principals (`uri`,`email`,`displayname`,`userid`) VALUES (?, ?, ?, ?);', 
			array('principals/'.$userModel->get('user_name'), $userModel->get('email1'), $displayname, $userID));
		
		$result = $adb->pquery('INSERT INTO dav_addressbooks (`principaluri`,`displayname`,`uri`,`description`) VALUES (?, ?, ?, ?);', 
			array('principals/'.$userModel->get('user_name'), $this->defaultName, $this->defaultName, ''));
		return $key;
	}
	
	public function deleteKey($params){
		global $adb;
		$adb->pquery('DELETE FROM dav_users WHERE userid = ?;', array( $params['user'] ));
		$adb->pquery('DELETE FROM dav_principals WHERE userid = ?;', array( $params['user']  ));
	}
}
