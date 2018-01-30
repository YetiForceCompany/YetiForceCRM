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
		$dataReader->close();
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
