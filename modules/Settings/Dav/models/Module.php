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
		return [
			'calendar' => (new App\Db\Query())->select(['calendarid', 'num' => new yii\db\Expression('COUNT(id)')])
				->from('dav_calendarobjects')
				->groupBy('calendarid')
				->createCommand()->queryAllByGroup(),
			'addressbook' => (new App\Db\Query())->select(['addressbookid', 'num' => new yii\db\Expression('COUNT(id)')])
				->from('dav_cards')
				->groupBy('addressbookid')
				->createCommand()->queryAllByGroup()
		];
	}

	public function addKey($params)
	{
		$query = new App\Db\Query();
		$type = (gettype($params['type']) == 'array') ? $params['type'] : [$params['type']];
		$userID = $params['user'];
		$query->select('id')
			->from('dav_users')
			->where(['userid' => $userID]);
		if ($query->exists()) {
			return 1;
		}
		$keyLength = 10;
		$key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $keyLength);
		$userModel = Users_Record_Model::getInstanceById($userID, 'Users');
		$digesta1 = md5($userModel->get('user_name') . ':YetiDAV:' . $key);
		$db = App\Db::getInstance();
		$result = $db->createCommand()->insert('dav_users', [
				'username' => $userModel->get('user_name'),
				'digesta1' => $digesta1,
				'key' => $key,
				'userid' => $userID
			])->execute();
		if (!$result)
			return 0;
		$displayname = $userModel->getName();
		$db->createCommand()->insert('dav_principals', [
			'uri' => 'principals/' . $userModel->get('user_name'),
			'email' => $userModel->get('email1'),
			'displayname' => $displayname,
			'userid' => $userID
		])->execute();
		if (in_array('CardDav', $type)) {
			$db->createCommand()->insert('dav_addressbooks', [
				'principaluri' => 'principals/' . $userModel->get('user_name'),
				'displayname' => API_CardDAV_Model::ADDRESSBOOK_NAME,
				'uri' => API_CardDAV_Model::ADDRESSBOOK_NAME,
				'description' => ''
			])->execute();
		}
		if (in_array('CalDav', $type)) {
			$db->createCommand()->insert('dav_calendars', [
				'principaluri' => 'principals/' . $userModel->get('user_name'),
				'displayname' => API_CalDAV_Model::CALENDAR_NAME,
				'uri' => API_CalDAV_Model::CALENDAR_NAME,
				'components' => API_CalDAV_Model::COMPONENTS
			])->execute();
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
		$db = App\Db::getInstance();
		$db->createCommand()->delete('dav_users', ['userid' => $params['user']])->execute();
		$db->createCommand()->delete('dav_principals', ['userid' => $params['user']])->execute();

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
		$user = Users_Record_Model::getInstanceById($params['user'], 'Users');
		$user_name = $user->get('user_name');
		$path = '/' . $user_name . '/';
		$davStorageDir = vglobal('davStorageDir');
		@mkdir($davStorageDir . $path);
	}
}
