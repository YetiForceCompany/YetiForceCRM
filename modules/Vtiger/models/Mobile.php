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

class Vtiger_Mobile_Model extends Vtiger_Base_Model
{

	public static function checkPermissionForOutgoingCall()
	{
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$result = $adb->pquery('SELECT id FROM yetiforce_mobile_keys WHERE user = ? AND service = ?;', array($currentUser->getId(), 'pushcall'));
		if ($adb->num_rows($result) > 0) {
			return true;
		}
		return false;
	}

	public function performCall($record = false, $phoneNumber = false, $user = false)
	{
		$adb = PearDatabase::getInstance();
		$return = false;
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$queryUser = $currentUser->getId();
		if ($user) {
			$queryUser = $user;
		}
		$result = $adb->pquery('DELETE FROM yetiforce_mobile_pushcall WHERE user = ?;', array($queryUser));
		if ($phoneNumber && $queryUser) {
			$result = $adb->pquery('INSERT INTO yetiforce_mobile_pushcall (`user`, `number`) VALUES (?, ?);', array($queryUser, $phoneNumber));
			$return = true;
		}
		return $return;
	}

	public function getAllMobileKeys($service, $userid = false)
	{
		$adb = PearDatabase::getInstance();

		$params = array('Active');
		$sql = '';
		if ($userid) {
			$sql .= ' AND vtiger_users.id <> ?';
			$params[] = $userid;
		}
		if ($service) {
			$sql .= ' AND yetiforce_mobile_keys.service = ?';
			$params[] = $service;
		}
		$result = $adb->pquery('SELECT yetiforce_mobile_keys.*, vtiger_users.user_name,' . getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . ' as fullusername, vtiger_users.id AS userid FROM yetiforce_mobile_keys INNER JOIN vtiger_users ON vtiger_users.id = yetiforce_mobile_keys.user WHERE vtiger_users.status = ? ' . $sql, $params);
		$rows = $adb->num_rows($result);
		$keys = Array();
		for ($i = 0; $i < $rows; $i++) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$keys[$row['id']] = $row;
			$keys[$row['id']]['name'] = 'LBL_MOBILE_' . strtoupper($row['service']);
			$privileges_users = unserialize($row['privileges_users']);
			$keys[$row['id']]['privileges_users'] = $privileges_users != '' ? $privileges_users : array();
		}
		return $keys;
	}

	public function getPrivilegesUsers()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$users = array();
		$keys = self::getAllMobileKeys('pushcall', $currentUser->getId());
		foreach ($keys as $id => $key) {
			if (in_array($currentUser->getId(), $key['privileges_users']))
				$users[$key['userid']] = $key['fullusername'];
		}
		return $users;
	}
}
