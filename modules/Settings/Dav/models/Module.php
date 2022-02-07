<?php

/**
 * Settings dav module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
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
				->createCommand()->queryAllByGroup(),
		];
	}

	/**
	 * Function to add key.
	 *
	 * @param string[] $type
	 * @param int      $userID
	 *
	 * @return int
	 */
	public function addKey($type, $userID)
	{
		$query = new App\Db\Query();
		$query->select(['id'])
			->from('dav_users')
			->where(['userid' => $userID]);
		if ($query->exists()) {
			return 1;
		}
		$keyLength = 10;
		$key = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $keyLength);
		$userModel = Users_Record_Model::getInstanceById($userID, 'Users');
		$digesta1 = md5($userModel->get('user_name') . ':YetiDAV:' . $key);
		$db = App\Db::getInstance();
		$result = $db->createCommand()->insert('dav_users', [
			'username' => $userModel->get('user_name'),
			'digesta1' => $digesta1,
			'key' => App\Encryption::getInstance()->encrypt($key),
			'userid' => $userID,
		])->execute();
		if (!$result) {
			return 0;
		}
		$displayname = $userModel->getName();
		$db->createCommand()->insert('dav_principals', [
			'uri' => 'principals/' . $userModel->get('user_name'),
			'email' => $userModel->get('email1'),
			'displayname' => $displayname,
			'userid' => $userID,
		])->execute();
		$pdo = $db->getMasterPdo();
		if (\in_array('CalDav', $type)) {
			$calendarBackend = new \App\Integrations\Dav\Backend\Calendar($pdo);
			$calendarBackend->createCalendar('principals/' . $userModel->get('user_name'), API_CalDAV_Model::CALENDAR_NAME, [
				'{DAV:}displayname' => API_CalDAV_Model::CALENDAR_NAME,
			]);
			$db->createCommand()->update('vtiger_activity', ['dav_status' => 1])->execute();
		}
		if (\in_array('CardDav', $type)) {
			$cardDavBackend = new \App\Integrations\Dav\Backend\Card($pdo);
			$cardDavBackend->createAddressBook('principals/' . $userModel->get('user_name'), API_CardDAV_Model::ADDRESSBOOK_NAME, [
				'{DAV:}displayname' => API_CardDAV_Model::ADDRESSBOOK_NAME,
			]);
			$db->createCommand()->update('vtiger_contactdetails', ['dav_status' => 1])->execute();
			$db->createCommand()->update('vtiger_ossemployees', ['dav_status' => 1])->execute();
		}
		if (\in_array('WebDav', $type)) {
			$this->createUserDirectory($userID);
		}
		return $key;
	}

	/**
	 * Function to delete key.
	 *
	 * @param int $userId
	 */
	public function deleteKey($userId)
	{
		$uri = (new \App\Db\Query())->select(['uri'])->from('dav_principals')->where(['userid' => $userId]);
		$calendarId = (new \App\Db\Query())->select(['calendarid'])->from('dav_calendarinstances')->where(['principaluri' => $uri]);
		$dbCommand = App\Db::getInstance()->createCommand();
		$dbCommand->delete('dav_calendars', ['id' => $calendarId])->execute();
		$dbCommand->delete('dav_calendarinstances', ['principaluri' => $uri])->execute();
		$dbCommand->delete('dav_addressbooks', ['principaluri' => $uri])->execute();
		$dbCommand->delete('dav_users', ['userid' => $userId])->execute();
		$dbCommand->delete('dav_principals', ['userid' => $userId])->execute();
		$userName = App\User::getUserModel($userId)->getDetail('user_name');
		$davStorageDir = App\Config::main('davStorageDir');
		vtlib\Functions::recurseDelete($davStorageDir . '/' . $userName);
	}

	public function getTypes()
	{
		return ['CalDav', 'CardDav', 'WebDav'];
	}

	/**
	 * Create directory for WebDav.
	 *
	 * @param int $userId
	 */
	public function createUserDirectory($userId)
	{
		@mkdir(App\Config::main('davStorageDir') . '/' . App\User::getUserModel($userId)->getDetail('user_name') . '/', 0777, true);
	}
}
