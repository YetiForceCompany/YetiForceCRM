<?php

/**
 * DAV model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class API_DAV_Model
{
	public $davUsers = [];

	public static function getAllUser($type = 0)
	{
		$db = new App\Db\Query();
		if (0 === $type) {
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
		} elseif (1 === $type) {
			$db->select([
				'david' => 'dav_users.id',
				'userid' => 'dav_users.userid',
				'addressbooksid' => 'dav_addressbooks.id',
			])->from('dav_users')
				->innerJoin('vtiger_users', 'vtiger_users.id = dav_users.userid')
				->innerJoin('dav_principals', 'dav_principals.userid = dav_users.userid')
				->innerJoin('dav_addressbooks', 'dav_addressbooks.principaluri = dav_principals.uri')
				->where(['vtiger_users.status' => 'Active']);
		} elseif (2 === $type) {
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
