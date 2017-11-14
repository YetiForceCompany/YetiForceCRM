<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/utils.php';
require_once 'include/runtime/Globals.php';
require_once 'include/runtime/Cache.php';

/** Function to delete the vtiger_role related sharing rules
 * @param $roleid -- RoleId :: Type varchar
 */
function deleteRoleRelatedSharingRules($roleId)
{

	\App\Log::trace('Entering deleteRoleRelatedSharingRules(' . $roleId . ') method ...');
	$dataShareTableColArr = [
		'vtiger_datashare_us2role' => 'to_roleid',
		'vtiger_datashare_us2rs' => 'to_roleandsubid',
		'vtiger_datashare_grp2role' => 'to_roleid',
		'vtiger_datashare_grp2rs' => 'to_roleandsubid',
		'vtiger_datashare_role2group' => 'share_roleid',
		'vtiger_datashare_role2us' => 'share_roleid',
		'vtiger_datashare_role2role' => 'share_roleid::to_roleid',
		'vtiger_datashare_role2rs' => 'share_roleid::to_roleandsubid',
		'vtiger_datashare_rs2grp' => 'share_roleandsubid',
		'vtiger_datashare_rs2us' => 'share_roleandsubid',
		'vtiger_datashare_rs2role' => 'share_roleandsubid::to_roleid',
		'vtiger_datashare_rs2rs' => 'share_roleandsubid::to_roleandsubid'
	];

	foreach ($dataShareTableColArr as $tablename => $colname) {
		$colNameArr = explode('::', $colname);
		$query = (new App\Db\Query())->select('shareid')
			->from($tablename)
			->where([$colNameArr[0] => $roleId]);
		if (sizeof($colNameArr) > 1) {
			$query->orWhere([$colNameArr[1] => $roleId]);
		}
		$dataReader = $query->createCommand()->query();
		while ($shareid = $dataReader->readColumn(0)) {
			deleteSharingRule($shareid);
		}
	}
	\App\Log::trace("Exiting deleteRoleRelatedSharingRules method ...");
}

/** Function to delete the group related sharing rules
 * @param $roleid -- RoleId :: Type varchar
 */
function deleteGroupRelatedSharingRules($grpId)
{

	\App\Log::trace("Entering deleteGroupRelatedSharingRules(" . $grpId . ") method ...");

	$adb = PearDatabase::getInstance();
	$dataShareTableColArr = [
		'vtiger_datashare_grp2grp' => 'share_groupid::to_groupid',
		'vtiger_datashare_grp2role' => 'share_groupid',
		'vtiger_datashare_grp2rs' => 'share_groupid',
		'vtiger_datashare_grp2us' => 'share_groupid',
		'vtiger_datashare_role2group' => 'to_groupid',
		'vtiger_datashare_rs2grp' => 'to_groupid'
	];


	foreach ($dataShareTableColArr as $tablename => $colname) {
		$colNameArr = explode('::', $colname);
		$query = sprintf("SELECT shareid FROM %s WHERE %s = ?", $tablename, $colNameArr[0]);
		$params = [$grpId];
		if (sizeof($colNameArr) > 1) {
			$query .= " or " . $colNameArr[1] . "=?";
			array_push($params, $grpId);
		}

		$result = $adb->pquery($query, $params);
		$numRows = $adb->numRows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$shareid = $adb->queryResult($result, $i, 'shareid');
			deleteSharingRule($shareid);
		}
	}
	\App\Log::trace('Exiting deleteGroupRelatedSharingRules method ...');
}

function deleteUserRelatedSharingRules($usId)
{

	\App\Log::trace("Entering deleteGroupRelatedSharingRules(" . $usId . ") method ...");

	$adb = PearDatabase::getInstance();
	$dataShareTableColArr = [
		'vtiger_datashare_us2us' => 'share_userid::to_userid',
		'vtiger_datashare_us2grp' => 'share_userid',
		'vtiger_datashare_us2role' => 'share_userid',
		'vtiger_datashare_us2rs' => 'share_userid',
		'vtiger_datashare_grp2us' => 'to_userid',
		'vtiger_datashare_rs2us' => 'to_userid',
		'vtiger_datashare_role2us' => 'to_userid'
	];


	foreach ($dataShareTableColArr as $tableName => $colName) {
		$colNameArr = explode('::', $colName);
		$query = sprintf("SELECT shareid FROM %s WHERE %s = ?", $tableName, $colNameArr[0]);
		$params = [$grpId];
		if (sizeof($colNameArr) > 1) {
			$query .= " or " . $colNameArr[1] . "=?";
			array_push($params, $grpId);
		}

		$result = $adb->pquery($query, $params);
		$numRows = $adb->numRows($result);
		for ($i = 0; $i < $numRows; $i++) {
			$shareid = $adb->queryResult($result, $i, 'shareid');
			deleteSharingRule($shareid);
		}
	}
	\App\Log::trace('Exiting deleteGroupRelatedSharingRules method ...');
}

/** This function is to delete the organisation level sharing rule
 * It takes the following input parameters:
 *     $shareid -- Id of the Sharing Rule to be updated
 */
function deleteSharingRule($shareid)
{

	\App\Log::trace("Entering deleteSharingRule(" . $shareid . ") method ...");
	$adb = PearDatabase::getInstance();
	$query2 = "select * from vtiger_datashare_module_rel where shareid=?";
	$res = $adb->pquery($query2, [$shareid]);
	$typestr = $adb->queryResult($res, 0, 'relationtype');
	$tabname = getDSTableNameForType($typestr);
	$query3 = "delete from $tabname where shareid=?";
	$adb->pquery($query3, [$shareid]);
	$query4 = "delete from vtiger_datashare_module_rel where shareid=?";
	$adb->pquery($query4, [$shareid]);

	//deleting the releated module sharing permission
	$query5 = "delete from vtiger_datashare_relatedmodule_permission where shareid=?";
	$adb->pquery($query5, [$shareid]);
	\App\Log::trace("Exiting deleteSharingRule method ...");
}

/** Function get the Data Share Table Names
 *  @returns the following Date Share Table Name Array:
 *  $dataShareTableColArr=Array('GRP::GRP'=>'datashare_grp2grp',
 * 				    'GRP::ROLE'=>'datashare_grp2role',
 * 				    'GRP::RS'=>'datashare_grp2rs',
 * 				    'ROLE::GRP'=>'datashare_role2group',
 * 				    'ROLE::ROLE'=>'datashare_role2role',
 * 				    'ROLE::RS'=>'datashare_role2rs',
 * 				    'RS::GRP'=>'datashare_rs2grp',
 * 				    'RS::ROLE'=>'datashare_rs2role',
 * 				    'RS::RS'=>'datashare_rs2rs');
 */
function getDataShareTableName()
{

	\App\Log::trace('Entering getDataShareTableName() method ...');
	$dataShareTableColArr = [
		'US::GRP' => 'vtiger_datashare_us2grp',
		'US::ROLE' => 'vtiger_datashare_us2role',
		'US::RS' => 'vtiger_datashare_us2rs',
		'US::US' => 'vtiger_datashare_us2us',
		'GRP::GRP' => 'vtiger_datashare_grp2grp',
		'GRP::ROLE' => 'vtiger_datashare_grp2role',
		'GRP::RS' => 'vtiger_datashare_grp2rs',
		'GRP::US' => 'vtiger_datashare_grp2us',
		'ROLE::GRP' => 'vtiger_datashare_role2group',
		'ROLE::ROLE' => 'vtiger_datashare_role2role',
		'ROLE::RS' => 'vtiger_datashare_role2rs',
		'ROLE::US' => 'vtiger_datashare_role2us',
		'RS::GRP' => 'vtiger_datashare_rs2grp',
		'RS::ROLE' => 'vtiger_datashare_rs2role',
		'RS::RS' => 'vtiger_datashare_rs2rs',
		'RS::US' => 'vtiger_datashare_rs2us'
	];
	\App\Log::trace('Exiting getDataShareTableName method ...');
	return $dataShareTableColArr;
}

/** Function to get the Data Share Table Name from the speciified type string
 *  @param $typeString -- Datashare Type Sting :: Type Varchar
 *  @returns Table Name -- Type Varchar
 *
 */
function getDSTableNameForType($typeString)
{

	\App\Log::trace("Entering getDSTableNameForType(" . $typeString . ") method ...");
	$dataShareTableColArr = getDataShareTableName();
	$tableName = $dataShareTableColArr[$typeString];
	\App\Log::trace("Exiting getDSTableNameForType method ...");
	return $tableName;
}

/** To retreive the subordinate vtiger_roles and vtiger_users of the specified parent vtiger_role
 * @param $roleid -- The Role Id:: Type varchar
 * @returns  subordinate vtiger_role array in the following format:
 *     $subordinateRoleUserArray=(roleid1=>Array(userid1,userid2,userid3),
  vtiger_roleid2=>Array(userid1,userid2,userid3)
  |
  |
  vtiger_roleidn=>Array(userid1,userid2,userid3));
 */
function getSubordinateRoleAndUsers($roleId)
{

	\App\Log::trace("Entering getSubordinateRoleAndUsers(" . $roleId . ") method ...");
	$subRoleAndUsers = [];
	$subordinateRoles = \App\PrivilegeUtil::getRoleSubordinates($roleId);
	foreach ($subordinateRoles as $subRoleId) {
		$userArray = \App\PrivilegeUtil::getUsersNameByRole($subRoleId);
		$subRoleAndUsers[$subRoleId] = $userArray;
	}
	\App\Log::trace("Exiting getSubordinateRoleAndUsers method ...");
	return $subRoleAndUsers;
}

function getWriteSharingGroupsList($module)
{

	\App\Log::trace("Entering getWriteSharingGroupsList(" . $module . ") method ...");
	$adb = PearDatabase::getInstance();
	$currentUser = vglobal('current_user');
	$grpArray = [];
	$tabId = \App\Module::getModuleId($module);
	$query = "select sharedgroupid from vtiger_tmp_write_group_sharing_per where userid=? and tabid=?";
	$result = $adb->pquery($query, [$currentUser->id, $tabId]);
	$numRows = $adb->numRows($result);
	for ($i = 0; $i < $numRows; $i++) {
		$grpId = $adb->queryResult($result, $i, 'sharedgroupid');
		$grpArray[] = $grpId;
	}
	$shareGrpList = constructList($grpArray, 'INTEGER');
	\App\Log::trace("Exiting getWriteSharingGroupsList method ...");
	return $shareGrpList;
}

function constructList($array, $data_type)
{

	\App\Log::trace("Entering constructList(" . $array . "," . $data_type . ") method ...");
	$list = [];
	if (sizeof($array) > 0) {
		$i = 0;
		foreach ($array as $value) {
			if ($data_type == "INTEGER") {
				array_push($list, $value);
			} elseif ($data_type == "VARCHAR") {
				array_push($list, "'" . $value . "'");
			}
			$i++;
		}
	}
	\App\Log::trace("Exiting constructList method ...");
	return $list;
}

function getListViewSecurityParameter($module)
{

	\App\Log::trace("Entering getListViewSecurityParameter(" . $module . ") method ...");
	$adb = PearDatabase::getInstance();

	$tabid = \App\Module::getModuleId($module);
	$current_user = vglobal('current_user');
	if ($current_user) {
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
	}
	if ($module == 'Leads') {
		$sec_query .= " and (
						vtiger_crmentity.smownerid in($current_user->id)
						or vtiger_crmentity.smownerid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%')
						or vtiger_crmentity.smownerid in(select shareduserid from vtiger_tmp_read_user_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ")
						or (";

		if (sizeof($current_user_groups) > 0) {
			$sec_query .= " vtiger_groups.groupid in (" . implode(",", $current_user_groups) . ") or ";
		}
		$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid from vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . "))) ";
	} elseif ($module == 'Accounts') {
		$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) " .
			"or vtiger_crmentity.smownerid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%') " .
			"or vtiger_crmentity.smownerid in(select shareduserid from vtiger_tmp_read_user_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ") or (";

		if (sizeof($current_user_groups) > 0) {
			$sec_query .= " vtiger_groups.groupid in (" . implode(",", $current_user_groups) . ") or ";
		}
		$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid from vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . "))) ";
	} elseif ($module == 'Contacts') {
		$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) " .
			"or vtiger_crmentity.smownerid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%') " .
			"or vtiger_crmentity.smownerid in(select shareduserid from vtiger_tmp_read_user_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ") or (";

		if (sizeof($current_user_groups) > 0) {
			$sec_query .= " vtiger_groups.groupid in (" . implode(",", $current_user_groups) . ") or ";
		}
		$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid from vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . "))) ";
	} elseif ($module == 'HelpDesk') {
		$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) or vtiger_crmentity.smownerid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%') or vtiger_crmentity.smownerid in(select shareduserid from vtiger_tmp_read_user_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ") ";

		$sec_query .= " or (";
		if (sizeof($current_user_groups) > 0) {
			$sec_query .= " vtiger_groups.groupid in (" . implode(",", $current_user_groups) . ") or ";
		}
		$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid from vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . "))) ";
	} elseif ($module === 'Calendar') {
		$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) or vtiger_crmentity.smownerid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%')";

		if (sizeof($current_user_groups) > 0) {
			$sec_query .= " or ((vtiger_groups.groupid in (" . implode(",", $current_user_groups) . ")))";
		}
		$sec_query .= ")";
	} elseif ($module === 'Campaigns') {

		$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) or vtiger_crmentity.smownerid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%') or vtiger_crmentity.smownerid in(select shareduserid from vtiger_tmp_read_user_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ") or ((";

		if (sizeof($current_user_groups) > 0) {
			$sec_query .= " vtiger_groups.groupid in (" . implode(",", $current_user_groups) . ") or ";
		}
		$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid from vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ")))) ";
	} elseif ($module == 'Documents') {
		$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) or vtiger_crmentity.smownerid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%') or vtiger_crmentity.smownerid in(select shareduserid from vtiger_tmp_read_user_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ") or ((";

		if (sizeof($current_user_groups) > 0) {
			$sec_query .= " vtiger_groups.groupid in (" . implode(",", $current_user_groups) . ") or ";
		}
		$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid from vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ")))) ";
	} elseif ($module == 'Products') {
		$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) " .
			"or vtiger_crmentity.smownerid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%') " .
			"or vtiger_crmentity.smownerid in(select shareduserid from vtiger_tmp_read_user_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ")";

		$sec_query .= " or (";

		if (sizeof($current_user_groups) > 0) {
			$sec_query .= " vtiger_groups.groupid in (" . implode(",", $current_user_groups) . ") or ";
		}
		$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid from vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . "))) ";
	} else {
		$modObj = CRMEntity::getInstance($module);
		$sec_query = $modObj->getListViewSecurityParameter($module);
	}
	\App\Log::trace("Exiting getListViewSecurityParameter method ...");
	return $sec_query;
}

/**
 * Function to get the permitted module id Array with presence as 0
 * @global Users $current_user
 * @return Array Array of accessible tabids.
 */
function getPermittedModuleIdList()
{
	$current_user = vglobal('current_user');
	$permittedModules = [];
	require('user_privileges/user_privileges_' . $current_user->id . '.php');
	include('user_privileges/tabdata.php');

	if ($is_admin === false && $profileGlobalPermission[1] == 1 &&
		$profileGlobalPermission[2] == 1) {
		foreach ($tab_seq_array as $tabid => $seq_value) {
			if ($seq_value === 0 && $profileTabsPermission[$tabid] === 0) {
				$permittedModules[] = ($tabid);
			}
		}
	} else {
		foreach ($tab_seq_array as $tabid => $seq_value) {
			if ($seq_value === 0) {
				$permittedModules[] = ($tabid);
			}
		}
	}
	$homeTabid = \App\Module::getModuleId('Home');
	if (!in_array($homeTabid, $permittedModules)) {
		$permittedModules[] = $homeTabid;
	}
	return $permittedModules;
}

/** Function to recalculate the Sharing Rules for all the vtiger_users
 * This function will recalculate all the sharing rules for all the vtiger_users in the Organization and will write them in flat vtiger_files
 *
 */
function RecalculateSharingRules()
{
	\App\Log::trace("Entering RecalculateSharingRules() method ...");
	$userIds = (new App\Db\Query())->select(['id'])
		->from('vtiger_users')
		->where(['deleted' => 0])
		->column();
	foreach ($userIds as $id) {
		\App\UserPrivilegesFile::createUserPrivilegesfile($id);
		\App\UserPrivilegesFile::createUserSharingPrivilegesfile($id);
	}
	\App\Log::trace("Exiting RecalculateSharingRules method ...");
}

/**
 *
 * @param String $module - module name for which query needs to be generated.
 * @param Users $user - user for which query needs to be generated.
 * @return String Access control Query for the user.
 */
function getNonAdminAccessControlQuery($module, Users $user, $scope = '')
{
	$instance = CRMEntity::getInstance($module);
	return $instance->getNonAdminAccessControlQuery($module, $user, $scope);
}
