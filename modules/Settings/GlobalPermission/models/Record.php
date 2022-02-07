<?php

/**
 * Settings GlobalPermission record model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_GlobalPermission_Record_Model extends Settings_Vtiger_Record_Model
{
	const GLOBAL_ACTION_VIEW = 1;
	const GLOBAL_ACTION_EDIT = 2;

	public function getId()
	{
	}

	public function getName()
	{
	}

	public static function getGlobalPermissions()
	{
		$dataReader = (new App\Db\Query())->from('vtiger_profile2globalpermissions')
			->leftJoin('vtiger_profile', 'vtiger_profile.profileid = vtiger_profile2globalpermissions.profileid')
			->createCommand()->query();
		$globalPermissions = [];
		while ($row = $dataReader->read()) {
			$profileid = $row['profileid'];
			$actionId = $row['globalactionid'];
			$permissionId = $row['globalactionpermission'];
			$profilename = $row['profilename'];
			$description = $row['description'];
			$globalPermissions[$profileid]['gp_' . $actionId] = $permissionId;
			$globalPermissions[$profileid]['profilename'] = $profilename;
			$globalPermissions[$profileid]['description'] = $description;
		}
		$dataReader->close();

		return $globalPermissions;
	}

	public static function save($profileID, $globalactionid, $checked)
	{
		if (1 == $globalactionid) {
			\App\Privilege::setAllUpdater();
		}
		$db = App\Db::getInstance();
		$db->createCommand()->delete('vtiger_profile2globalpermissions', ['profileid' => $profileID, 'globalactionid' => $globalactionid])->execute();
		$db->createCommand()->insert('vtiger_profile2globalpermissions', [
			'profileid' => $profileID,
			'globalactionid' => $globalactionid,
			'globalactionpermission' => $checked,
		])->execute();
		self::recalculate();
	}

	public static function recalculate()
	{
		$php_max_execution_time = \App\Config::main('php_max_execution_time');
		set_time_limit($php_max_execution_time);
		$userIdsList = Settings_Profiles_Record_Model::getUsersList();
		if ($userIdsList) {
			foreach ($userIdsList as $userId) {
				\App\UserPrivilegesFile::createUserPrivilegesfile($userId);
			}
		}
	}
}
