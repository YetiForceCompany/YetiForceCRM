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

class Settings_GlobalPermission_Record_Model extends Settings_Vtiger_Record_Model
{

	const GLOBAL_ACTION_VIEW = 1;
	const GLOBAL_ACTION_EDIT = 2;

	public function getId()
	{
		return;
	}

	public function getName()
	{
		return;
	}

	public static function getGlobalPermissions()
	{
		$dataReader = (new App\Db\Query())->from('vtiger_profile2globalpermissions')
			->leftJoin('vtiger_profile', 'vtiger_profile.profileid = vtiger_profile2globalpermissions.profileid')
			->createCommand()->query();
		$globalPermissions = [];
		while($row = $dataReader->read()) {
			$profileid = $row['profileid'];
			$actionId = $row['globalactionid'];
			$permissionId = $row['globalactionpermission'];
			$profilename = $row['profilename'];
			$description =$row['description'];
			$globalPermissions[$profileid]['gp_' . $actionId] = $permissionId;
			$globalPermissions[$profileid]['profilename'] = $profilename;
			$globalPermissions[$profileid]['description'] = $description;
		}
		return $globalPermissions;
	}

	public static function save($profileID, $globalactionid, $checked)
	{
		if ($globalactionid == 1) {
			\App\Privilege::setAllUpdater();
		}
		$db = App\Db::getInstance();
		$db->createCommand()->delete('vtiger_profile2globalpermissions', ['profileid' => $profileID, 'globalactionid' => $globalactionid])->execute();
		$db->createCommand()->insert('vtiger_profile2globalpermissions', [
			'profileid' => $profileID,
			'globalactionid' => $globalactionid,
			'globalactionpermission' => $checked
		])->execute();
		self::recalculate();
	}

	public static function recalculate()
	{
		$php_max_execution_time = vglobal('php_max_execution_time');
		set_time_limit($php_max_execution_time);
		vimport('~~modules/Users/CreateUserPrivilegeFile.php');
		$userIdsList = Settings_Profiles_Record_Model::getUsersList();
		if ($userIdsList) {
			foreach ($userIdsList as $userId) {
				createUserPrivilegesfile($userId);
			}
		}
	}
}
