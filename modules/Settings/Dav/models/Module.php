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

class Settings_Dav_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getAllKeys()
	{
		return API_DAV_Model::getAllUser();
	}

	public function getAmountData()
	{
		$adb = PearDatabase::getInstance();
		$addressbook = $calendarid = [];
		$result = $adb->query('SELECT addressbookid, COUNT(id) AS num FROM dav_cards GROUP BY addressbookid;');
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$addressbook[$adb->query_result_raw($result, $i, 'addressbookid')] = $adb->query_result_raw($result, $i, 'num');
		}
		$result = $adb->query('SELECT calendarid, COUNT(id) AS num FROM dav_calendarobjects GROUP BY calendarid;');
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$calendarid[$adb->query_result_raw($result, $i, 'calendarid')] = $adb->query_result_raw($result, $i, 'num');
		}
		return ['calendar' => $calendarid, 'addressbook' => $addressbook];
	}

	public function addKey($params)
	{
		$adb = PearDatabase::getInstance();
		$type = (gettype($params['type']) == 'array') ? $params['type'] : [$params['type']];
		$userID = $params['user'];
		$result = $adb->pquery("SELECT id FROM dav_users WHERE userid = ?;", array($userID), true);
		$rows = $adb->num_rows($result);
		if ($rows != 0) {
			return 1;
		}
		$keyLength = 10;
		$key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $keyLength);
		$userModel = Users_Record_Model::getInstanceById($userID, 'Users');
		$digesta1 = md5($userModel->get('user_name') . ':YetiDAV:' . $key);
		$result = $adb->pquery('INSERT INTO dav_users (`username`, `digesta1`, `key`, `userid`) VALUES (?, ?, ?, ?);', array($userModel->get('user_name'), $digesta1, $key, $userID));
		if (!$result)
			return 0;
		$displayname = $userModel->getName();
		$result = $adb->pquery('INSERT INTO dav_principals (`uri`,`email`,`displayname`,`userid`) VALUES (?, ?, ?, ?);', array('principals/' . $userModel->get('user_name'), $userModel->get('email1'), $displayname, $userID));


		if (in_array('CardDav', $type)) {
			$result = $adb->pquery('INSERT INTO dav_addressbooks (`principaluri`,`displayname`,`uri`,`description`) VALUES (?, ?, ?, ?);', array('principals/' . $userModel->get('user_name'), API_CardDAV_Model::ADDRESSBOOK_NAME, API_CardDAV_Model::ADDRESSBOOK_NAME, ''));
		}
		if (in_array('CalDav', $type)) {
			$result = $adb->pquery('INSERT INTO dav_calendars (`principaluri`,`displayname`,`uri`,`components`) VALUES (?, ?, ?, ?);', array('principals/' . $userModel->get('user_name'), API_CalDAV_Model::CALENDAR_NAME, API_CalDAV_Model::CALENDAR_NAME, API_CalDAV_Model::COMPONENTS));
		}
		if (in_array('WebDav', $type)) {
			$this->createUserDirectory($params);
		}
		return $key;
	}

	public function deleteKey($params)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery('DELETE dav_calendars FROM dav_calendars LEFT JOIN dav_principals ON dav_calendars.principaluri = dav_principals.uri WHERE dav_principals.userid = ?;', array($params['user']));
		$adb->pquery('DELETE FROM dav_users WHERE userid = ?;', array($params['user']));
		$adb->pquery('DELETE FROM dav_principals WHERE userid = ?;', array($params['user']));

		$user = Users_Record_Model::getInstanceById($params['user'], 'Users');
		$user_name = $user->get('user_name');
		$davStorageDir = vglobal('davStorageDir');
		vtlib\Functions::recurseDelete($davStorageDir . '/' . $user_name);
	}

	public function getTypes()
	{
		return ['CalDav', 'CardDav', 'WebDav'];
	}

	public function createUserDirectory($params)
	{
		$adb = PearDatabase::getInstance();
		$user = Users_Record_Model::getInstanceById($params['user'], 'Users');
		$user_name = $user->get('user_name');

		$path = '/' . $user_name . '/';
		$dirHash = sha1($path);
		$parent_dirid = 0;
		$davStorageDir = vglobal('davStorageDir');
		@mkdir($davStorageDir . $path);

	}
}
