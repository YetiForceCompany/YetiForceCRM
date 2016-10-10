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
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_profile2globalpermissions LEFT JOIN vtiger_profile ON vtiger_profile.profileid = vtiger_profile2globalpermissions.profileid', array());
		for ($i = 0; $i < $db->num_rows($result); ++$i) {
			$profileid = $db->query_result($result, $i, 'profileid');
			$actionId = $db->query_result($result, $i, 'globalactionid');
			$permissionId = $db->query_result($result, $i, 'globalactionpermission');
			$profilename = $db->query_result($result, $i, 'profilename');
			$description = $db->query_result($result, $i, 'description');
			$globalPermissions[$profileid]['gp_' . $actionId] = $permissionId;
			$globalPermissions[$profileid]['profilename'] = $profilename;
			$globalPermissions[$profileid]['description'] = $description;
		}
		return $globalPermissions;
	}

	public static function save($profileID, $globalactionid, $checked)
	{
		if ($globalactionid == 1) {
			\includes\Privileges::setAllUpdater();
		}
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_profile2globalpermissions WHERE profileid=? && globalactionid=?', array($profileID, $globalactionid));
		$sql = 'INSERT INTO vtiger_profile2globalpermissions(profileid, globalactionid, globalactionpermission) VALUES (?,?,?)';
		$db->pquery($sql, array($profileID, $globalactionid, $checked));
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
