<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class API_DAV_Model
{

	public $log = '';
	public $davUsers = [];

	public function runCronCardDav($log)
	{
		$dav = new self();
		$dav->log = $log;
		$dav->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start CardDAV Sync ');
		$crmUsers = Users_Record_Model::getAll();
		$davUsers = $dav->getAllUser(1);
		foreach ($crmUsers as $key => $user) {
			if (array_key_exists($key, $davUsers)) {
				$user->set('david', $davUsers[$key]['david']);
				$user->set('addressbooksid', $davUsers[$key]['addressbooksid']);
				$dav->davUsers[$key] = $user;
				$dav->log->debug(__CLASS__ . '::' . __METHOD__ . ' | User is active ' . $user->getName());
			} else { // User is inactive
				$dav->log->warn(__CLASS__ . '::' . __METHOD__ . ' | User is inactive ' . $user->getName());
			}
		}
		$cardDav = new API_CardDAV_Model();
		$cardDav->log = $dav->log;
		$cardDav->davUsers = $dav->davUsers;
		$cardDav->cardDavCrm2Dav();
		$cardDav->cardDav2Crm();
		$dav->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End CardDAV Sync ');
	}

	public function runCronCalDav($log)
	{
		$dav = new self();
		$dav->log = $log;
		$dav->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start CalDAV Sync ');
		$crmUsers = Users_Record_Model::getAll();
		$davUsers = $dav->getAllUser(2);
		foreach ($crmUsers as $key => $user) {
			if (array_key_exists($key, $davUsers)) {
				$user->set('david', $davUsers[$key]['david']);
				$user->set('calendarsid', $davUsers[$key]['calendarsid']);
				$dav->davUsers[$key] = $user;
				$dav->log->debug(__CLASS__ . '::' . __METHOD__ . ' | User is active ' . $user->getName());
			} else { // User is inactive
				$dav->log->warn(__CLASS__ . '::' . __METHOD__ . ' | User is inactive ' . $user->getName());
			}
		}
		$cardDav = new API_CalDAV_Model();
		$cardDav->log = $dav->log;
		$cardDav->davUsers = $dav->davUsers;
		$cardDav->calDavCrm2Dav();
		$cardDav->calDav2Crm();
		$dav->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End CalDAV Sync ');
	}

	public function getAllUser($type = 0)
	{
		$db = PearDatabase::getInstance();
		if ($type == 0) {
			$sql = 'SELECT dav_users.*,dav_addressbooks.id AS addressbooksid, dav_calendars.id AS calendarsid, dav_principals.email, dav_principals.displayname, vtiger_users.status, vtiger_users.id AS userid, vtiger_users.user_name '
				. 'FROM dav_users '
				. 'INNER JOIN vtiger_users ON vtiger_users.id = dav_users.userid '
				. 'INNER JOIN dav_principals ON dav_principals.userid = dav_users.userid '
				. 'LEFT JOIN dav_addressbooks ON dav_addressbooks.principaluri = dav_principals.uri '
				. 'LEFT JOIN dav_calendars ON dav_calendars.principaluri = dav_principals.uri;';
		} elseif ($type == 1) {
			$sql = "SELECT dav_users.id AS david, dav_users.userid AS userid, dav_addressbooks.id AS addressbooksid FROM dav_users"
				. " INNER JOIN vtiger_users ON vtiger_users.id = dav_users.userid"
				. " INNER JOIN dav_principals ON dav_principals.userid = dav_users.userid"
				. " INNER JOIN dav_addressbooks ON dav_addressbooks.principaluri = dav_principals.uri"
				. " WHERE vtiger_users.status = 'Active';";
		} elseif ($type == 2) {
			$sql = "SELECT dav_users.id AS david, dav_users.userid AS userid, dav_calendars.id AS calendarsid FROM dav_users"
				. " INNER JOIN vtiger_users ON vtiger_users.id = dav_users.userid"
				. " INNER JOIN dav_principals ON dav_principals.userid = dav_users.userid"
				. " INNER JOIN dav_calendars ON dav_calendars.principaluri = dav_principals.uri"
				. " WHERE vtiger_users.status = 'Active';";
		}
		$result = $db->query($sql);
		$rows = $db->num_rows($result);
		$users = Array();
		for ($i = 0; $i < $rows; $i++) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$users[$row['userid']] = $row;
		}
		return $users;
	}
}
