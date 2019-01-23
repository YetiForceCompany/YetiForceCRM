<?php

/**
 * DAV model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class API_DAV_Model
{
	public $davUsers = [];

	public static function runCronCardDav()
	{
		$dav = new self();
		\App\Log::trace(__METHOD__ . ' | Start CardDAV Sync ');
		$davUsers = self::getAllUser(1);
		foreach (Users_Record_Model::getAll() as $id => $user) {
			if (isset($davUsers[$id])) {
				$user->set('david', $davUsers[$id]['david']);
				$user->set('addressbooksid', $davUsers[$id]['addressbooksid']);
				$user->set('groups', \App\User::getUserModel($id)->getGroups());
				$dav->davUsers[$id] = $user;
				\App\Log::trace(__METHOD__ . ' | User is active ' . $user->getName());
			} else { // User is inactive
				\App\Log::info(__METHOD__ . ' | User is inactive ' . $user->getName());
			}
		}
		$cardDav = new API_CardDAV_Model();
		$cardDav->davUsers = $dav->davUsers;
		$cardDav->cardDavCrm2Dav();
		$cardDav->cardDav2Crm();
		\App\Log::trace(__METHOD__ . ' | End CardDAV Sync ');
	}

	public static function runCronCalDav()
	{
		$dav = new self();
		\App\Log::trace(__METHOD__ . ' | Start CalDAV Sync ');
		$davUsers = self::getAllUser(2);
		foreach (Users_Record_Model::getAll() as $id => $user) {
			if (isset($davUsers[$id])) {
				$user->set('david', $davUsers[$id]['david']);
				$user->set('calendarsid', $davUsers[$id]['calendarsid']);
				$user->set('groups', \App\User::getUserModel($id)->getGroups());
				$dav->davUsers[$id] = $user;
				\App\Log::trace(__METHOD__ . ' | User is active ' . $user->getName());
			} else { // User is inactive
				\App\Log::info(__METHOD__ . ' | User is inactive ' . $user->getName());
			}
		}
		$cardDav = new API_CalDAV_Model();
		$cardDav->davUsers = $dav->davUsers;
		$cardDav->calDavCrm2Dav();
		$cardDav->calDav2Crm();
		\App\Log::trace(__METHOD__ . ' | End CalDAV Sync ');
	}

	public static function getAllUser($type = 0)
	{
		$db = new App\Db\Query();
		if ($type === 0) {
			$db->select([
				'dav_users.*',
				'addressbooksid' => 'dav_addressbooks.id',
				'calendarsid' => 'dav_calendarinstances.calendarid',
				'dav_principals.email',
				'dav_principals.displayname',
				'vtiger_users.status',
				'userid' => 'vtiger_users.id',
				'vtiger_users.user_name',
			])->from('dav_users')
				->innerJoin('vtiger_users', 'vtiger_users.id = dav_users.userid')
				->innerJoin('dav_principals', 'dav_principals.userid = dav_users.userid')
				->leftJoin('dav_addressbooks', 'dav_addressbooks.principaluri = dav_principals.uri')
				->leftJoin('dav_calendarinstances', 'dav_calendarinstances.principaluri = dav_principals.uri');
		} elseif ($type === 1) {
			$db->select([
				'david' => 'dav_users.id',
				'userid' => 'dav_users.userid',
				'addressbooksid' => 'dav_addressbooks.id',
			])->from('dav_users')
				->innerJoin('vtiger_users', 'vtiger_users.id = dav_users.userid')
				->innerJoin('dav_principals', 'dav_principals.userid = dav_users.userid')
				->innerJoin('dav_addressbooks', 'dav_addressbooks.principaluri = dav_principals.uri')
				->where(['vtiger_users.status' => 'Active']);
		} elseif ($type === 2) {
			$db->select([
				'david' => 'dav_users.id',
				'userid' => 'dav_users.userid',
				'calendarsid' => 'dav_calendarinstances.calendarid',
			])->from('dav_users')
				->innerJoin('vtiger_users', 'vtiger_users.id = dav_users.userid')
				->innerJoin('dav_principals', 'dav_principals.userid = dav_users.userid')
				->innerJoin('dav_calendarinstances', 'dav_calendarinstances.principaluri = dav_principals.uri')
				->where(['vtiger_users.status' => 'Active']);
		}
		$dataReader = $db->createCommand()->query();
		$users = [];
		while ($row = $dataReader->read()) {
			$users[$row['userid']] = $row;
		}
		$dataReader->close();

		return $users;
	}
}
