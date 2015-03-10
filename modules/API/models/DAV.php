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
class API_DAV_Model {
	public $log = '';
	
	public function runCronCardDAV($log) {
		$dav = new self();
		$dav->log = $log;
		$crmUsers = Users_Record_Model::getAll();
		$davUsers = $dav->getAllUser(1);
		foreach($crmUsers as $key => $user) {
			if (array_key_exists($key,$davUsers)){
				$dav->log->debug( __CLASS__ . '::' . __METHOD__ . ' | Start CardDAV Sync for user '.$user->getName());
				$cardDav = new API_CardDAV_Model($user,$dav->log);
				$cardDav->getAddressBookId();
				$cardDav->cardDavCrm2Dav();
				$cardDav->cardDavDav2Crm();
				$dav->log->debug( __CLASS__ . '::' . __METHOD__ . ' | End CardDAV Sync ');
			}else{
				$dav->log->warn( __CLASS__ . '::' . __METHOD__ . ' | User is inactive '.$user->getName());
				// User is inactive
			}
		}
	}
	
	public function getAllUser($type = 0) {
		$db = PearDatabase::getInstance();
		if($type == 0){
			$sql = 'SELECT dav_users.*, dav_principals.email, dav_principals.displayname, vtiger_users.status, vtiger_users.id AS userid, vtiger_users.user_name FROM dav_users INNER JOIN vtiger_users ON vtiger_users.id = dav_users.userid LEFT JOIN dav_principals ON dav_principals.userid = dav_users.userid';
		}elseif($type == 1){
			$sql = "SELECT dav_users.userid AS id FROM dav_users INNER JOIN vtiger_users ON vtiger_users.id = dav_users.userid WHERE vtiger_users.status = 'Active';";
		}
		$result = $db->query($sql);
        $rows = $db->num_rows($result);
		$users = Array();
        for($i=0; $i<$rows; $i++){
			$row = $db->raw_query_result_rowdata($result, $i);
			$users[ $row['id'] ] = $row;
        }
		return $users;
	}
}