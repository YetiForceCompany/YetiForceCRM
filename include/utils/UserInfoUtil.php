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
require_once 'include/utils/GetUserGroups.php';
require_once 'include/events/include.php';
require_once 'include/runtime/Globals.php';
require_once 'include/runtime/Cache.php';

/** Function to get the lists of groupids releated with an user
 * This function accepts the user id as arguments and
 * returns the groupids related with the user id
 * as a comma seperated string
 */
function fetchUserGroupids($userid)
{

	\App\Log::trace("Entering fetchUserGroupids(" . $userid . ") method ...");
	$adb = PearDatabase::getInstance();
	$focus = new GetUserGroups();
	$focus->getAllUserGroups($userid);
	//Asha: Remove implode if not required and if so, also remove explode functions used at the recieving end of this function
	$groupidlists = implode(",", $focus->user_groups);
	\App\Log::trace("Exiting fetchUserGroupids method ...");
	return $groupidlists;
}

/** Function to get all the vtiger_tab utility action permission for the specified vtiger_profile
 * @param $profileid -- Profile Id:: Type integer
 * @returns  Tab Utility Action Permission Array in the following format:
 * $tabPermission = Array($tabid1=>Array(actionid1=>permission, actionid2=>permission,...,actionidn=>permission),
 *                        $tabid2=>Array(actionid1=>permission, actionid2=>permission,...,actionidn=>permission),
 *                                |
 *                        $tabidn=>Array(actionid1=>permission, actionid2=>permission,...,actionidn=>permission))
 *
 */
function getTabsUtilityActionPermission($profileid)
{

	\App\Log::trace("Entering getTabsUtilityActionPermission(" . $profileid . ") method ...");

	$adb = PearDatabase::getInstance();
	$check = [];
	$temp_tabid = [];
	$sql1 = "select * from vtiger_profile2utility where profileid=? order by(tabid)";
	$result1 = $adb->pquery($sql1, array($profileid));
	$num_rows1 = $adb->num_rows($result1);
	for ($i = 0; $i < $num_rows1; $i++) {
		$tab_id = $adb->query_result($result1, $i, 'tabid');
		if (!in_array($tab_id, $temp_tabid)) {
			$temp_tabid[] = $tab_id;
			$access = [];
		}

		$action_id = $adb->query_result($result1, $i, 'activityid');
		$per_id = $adb->query_result($result1, $i, 'permission');
		$access[$action_id] = $per_id;
		$check[$tab_id] = $access;
	}

	\App\Log::trace("Exiting getTabsUtilityActionPermission method ...");
	return $check;
}

/** Function to check if the currently logged in user is permitted to perform the specified action
 * @param $module -- Module Name:: Type varchar
 * @param $actionname -- Action Name:: Type varchar
 * @param $recordid -- Record Id:: Type integer
 * @returns yes or no. If Yes means this action is allowed for the currently logged in user. If no means this action is not allowed for the currently logged in user
 *
 */
function isPermitted($module, $actionname, $record_id = '')
{

	\App\Log::trace("Entering isPermitted($module,$actionname,$record_id) method ...");

	$current_user = vglobal('current_user');
	$userPrivileges = App\User::getPrivilegesFile($current_user->id);

	$permission = 'no';
	if (($module == 'Users' || $module == 'Home' || $module == 'uploads') && AppRequest::get('parenttab') != 'Settings') {
		//These modules dont have security right now
		vglobal('isPermittedLog', 'SEC_MODULE_DONT_HAVE_SECURITY_RIGHT');
		\App\Log::trace('Exiting isPermitted method ...');
		return 'yes';
	}

	//Checking the Access for the Settings Module
	if ($module == 'Settings' || $module == 'Administration' || $module == 'System' || AppRequest::get('parenttab') == 'Settings') {
		if (!$userPrivileges['is_admin']) {
			$permission = 'no';
		} else {
			$permission = 'yes';
		}
		vglobal('isPermittedLog', 'SEC_ADMINISTRATION_MODULE_' . strtoupper($permission));
		\App\Log::trace('Exiting isPermitted method ...');
		return $permission;
	}

	//Retreiving the Tabid and Action Id
	$tabid = \App\Module::getModuleId($module);
	$actionid = getActionid($actionname);
	$checkModule = $module;

	if ($checkModule == 'Events') {
		$checkModule = 'Calendar';
	}

	if (\App\Module::isModuleActive($checkModule)) {

		//Checking whether the user is admin
		if ($userPrivileges['is_admin']) {
			vglobal('isPermittedLog', 'SEC_USER_IS_ADMIN');
			\App\Log::trace('Exiting isPermitted method ...');
			return 'yes';
		}

		//If no actionid, then allow action is vtiger_tab permission is available
		if ($actionid === '' || $actionid === null) {
			if ($userPrivileges['profile_tabs_permission'][$tabid] == 0) {
				$permission = 'yes';
			} else {
				$permission = 'no';
			}
			vglobal('isPermittedLog', 'SEC_NO_ACTION_MODULE_PERMISSIONS_' . strtoupper($permission));
			\App\Log::trace('Exiting isPermitted method ...');
			return $permission;
		}
		//Checking for vtiger_tab permission
		if ($userPrivileges['profile_tabs_permission'][$tabid] != 0) {
			vglobal('isPermittedLog', 'SEC_MODULE_PERMISSIONS_NO');
			\App\Log::trace('Exiting isPermitted method ... - no');
			return 'no';
		}

		if ($actionid === false) {
			vglobal('isPermittedLog', 'SEC_ACTION_DOES_NOT_EXIST');
			\App\Log::trace('Exiting isPermitted method ... - no');
			return 'no';
		}
		//Checking for Action Permission
		if (!isset($userPrivileges['profile_action_permission'][$tabid][$actionid])) {
			vglobal('isPermittedLog', 'SEC_MODULE_NO_ACTION_TOOL');
			\App\Log::trace('Exiting isPermitted method ... - no');
			return 'no';
		}
		if (strlen($userPrivileges['profile_action_permission'][$tabid][$actionid]) < 1 && $userPrivileges['profile_action_permission'][$tabid][$actionid] == '') {
			vglobal('isPermittedLog', 'SEC_MODULE_RIGHTS_TO_ACTION');
			\App\Log::trace('Exiting isPermitted method ...');
			return 'yes';
		}

		if ($userPrivileges['profile_action_permission'][$tabid][$actionid] != 0 && $userPrivileges['profile_action_permission'][$tabid][$actionid] != '') {
			vglobal('isPermittedLog', 'SEC_MODULE_NO_RIGHTS_TO_ACTION');
			\App\Log::trace('Exiting isPermitted method ... - no');
			return 'no';
		}
		//Checking for view all permission
		if ($userPrivileges['profile_global_permission'][1] == 0 || $userPrivileges['profile_global_permission'][2] == 0) {
			if ($actionid == 3 || $actionid == 4) {
				vglobal('isPermittedLog', 'SEC_MODULE_VIEW_ALL_PERMISSION');
				\App\Log::trace('Exiting isPermitted method ...');
				return 'yes';
			}
		}
		//Checking for edit all permission
		if ($userPrivileges['profile_global_permission'][2] == 0) {
			if ($actionid == 3 || $actionid == 4 || $actionid == 0 || $actionid == 1) {
				vglobal('isPermittedLog', 'SEC_MODULE_EDIT_ALL_PERMISSION');
				\App\Log::trace('Exiting isPermitted method ...');
				return 'yes';
			}
		}
		//Checking and returning true if recorid is null
		if ($record_id == '') {
			vglobal('isPermittedLog', 'SEC_RECORID_IS_NULL');
			\App\Log::trace('Exiting isPermitted method ...');
			return 'yes';
		}

		//If modules is Products,Vendors,Faq,PriceBook then no sharing
		if ($record_id != '') {
			if (\vtlib\Functions::getModuleOwner($module) == 1) {
				vglobal('isPermittedLog', 'SEC_MODULE_IS_OWNEDBY');
				\App\Log::trace('Exiting isPermitted method ...');
				return 'yes';
			}
		}

		$recordMetaData = vtlib\Functions::getCRMRecordMetadata($record_id);
		if (!isset($recordMetaData) || $recordMetaData['deleted'] == 1) {
			vglobal('isPermittedLog', 'SEC_RECORD_DOES_NOT_EXIST');
			\App\Log::trace('Exiting isPermitted method ... - no');
			return 'no';
		}

		//Retreiving the RecordOwnerId
		$recOwnType = '';
		$recOwnId = '';
		$recordOwnerArr = getRecordOwnerId($record_id);

		foreach ($recordOwnerArr as $type => $id) {
			$recOwnType = $type;
			$recOwnId = $id;
		}
		if (\AppConfig::security('PERMITTED_BY_SHARED_OWNERS')) {
			$shownerids = Vtiger_SharedOwner_UIType::getSharedOwners($record_id, $module);
			if (in_array($current_user->id, $shownerids) || count(array_intersect($shownerids, $userPrivileges['groups'])) > 0) {
				vglobal('isPermittedLog', 'SEC_RECORD_SHARED_OWNER');
				\App\Log::trace('Exiting isPermitted method ... - Shared Owner');
				return 'yes';
			}
		}
		if ($recOwnType == 'Users') {
			//Checking if the Record Owner is the current User
			if ($current_user->id == $recOwnId) {
				vglobal('isPermittedLog', 'SEC_RECORD_OWNER_CURRENT_USER');
				\App\Log::trace('Exiting isPermitted method ...');
				return 'yes';
			}

			if (\AppConfig::security('PERMITTED_BY_ROLES')) {
				//Checking if the Record Owner is the Subordinate User
				foreach ($userPrivileges['subordinate_roles_users'] as $roleid => $userids) {
					if (in_array($recOwnId, $userids)) {
						vglobal('isPermittedLog', 'SEC_RECORD_OWNER_SUBORDINATE_USER');
						\App\Log::trace('Exiting isPermitted method ...');
						return 'yes';
					}
				}
			}
		} elseif ($recOwnType == 'Groups') {
			//Checking if the record owner is the current user's group
			if (in_array($recOwnId, $userPrivileges['groups'])) {
				vglobal('isPermittedLog', 'SEC_RECORD_OWNER_CURRENT_GROUP');
				\App\Log::trace('Exiting isPermitted method ...');
				return 'yes';
			}
		}
		if (\AppConfig::security('PERMITTED_BY_RECORD_HIERARCHY')) {
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			$role = $userPrivilegesModel->getRoleDetail();
			if ((($actionid == 3 || $actionid == 4) && $role->get('previewrelatedrecord') != 0 ) || (($actionid == 0 || $actionid == 1) && $role->get('editrelatedrecord') != 0 )) {
				$parentRecord = Users_Privileges_Model::getParentRecord($record_id, $module, $role->get('previewrelatedrecord'), $actionid);
				if ($parentRecord) {
					$recordMetaData = vtlib\Functions::getCRMRecordMetadata($parentRecord);
					$permissionsRoleForRelatedField = $role->get('permissionsrelatedfield');
					$permissionsRelatedField = $permissionsRoleForRelatedField == '' ? [] : explode(',', $role->get('permissionsrelatedfield'));
					$relatedPermission = false;
					foreach ($permissionsRelatedField as &$row) {
						switch ($row) {
							case 0:
								$relatedPermission = $recordMetaData['smownerid'] == $current_user->id || in_array($recordMetaData['smownerid'], $userPrivileges['groups']);
								break;
							case 1:
								$relatedPermission = in_array($current_user->id, Vtiger_SharedOwner_UIType::getSharedOwners($parentRecord, $recordMetaData['setype']));
								break;
							case 2:
								if (\AppConfig::security('PERMITTED_BY_SHARING')) {
									$permission = isPermittedBySharing($recordMetaData['setype'], \App\Module::getModuleId($recordMetaData['setype']), $actionid, $parentRecord);
									$relatedPermission = $permission == 'yes' ? true : false;
								}
								break;
						}
						if ($relatedPermission) {
							vglobal('isPermittedLog', 'SEC_RECORD_HIERARCHY_USER');
							\App\Log::trace('Exiting isPermitted method ... - Parent Record Owner');
							return 'yes';
						}
					}
				}
			}
		}
		if (\AppConfig::security('PERMITTED_BY_SHARING')) {
			$permission = isPermittedBySharing($module, $tabid, $actionid, $record_id);
		}
		vglobal('isPermittedLog', 'SEC_RECORD_BY_SHARING_' . strtoupper($permission));
		\App\Log::trace('Exiting isPermitted method ... - isPermittedBySharing');
	} else {
		$permission = 'no';
		vglobal('isPermittedLog', 'SEC_MODULE_IS_INACTIVE');
	}

	\App\Log::trace('Exiting isPermitted method ...');
	return $permission;
}

function isPermittedBySharing($module, $tabid, $actionid, $record_id)
{
	$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
	$defaultOrgSharingPermission = $userPrivilegesModel->get('defaultOrgSharingPermission');
	//Retreiving the default Organisation sharing Access
	$othersPermissionId = $defaultOrgSharingPermission[$tabid];
	//Checking for Default Org Sharing permission
	if ($othersPermissionId == 0) {
		if ($actionid == 1 || $actionid == 0) {
			return isReadWritePermittedBySharing($module, $tabid, $actionid, $record_id);
		} elseif ($actionid == 2) {
			return 'no';
		} else {
			return 'yes';
		}
	} elseif ($othersPermissionId == 1) {
		if ($actionid == 2) {
			return 'no';
		} else {
			return 'yes';
		}
	} elseif ($othersPermissionId == 2) {
		return 'yes';
	} elseif ($othersPermissionId == 3) {
		if ($actionid == 3 || $actionid == 4) {
			return isReadPermittedBySharing($module, $tabid, $actionid, $record_id);
		} elseif ($actionid == 0 || $actionid == 1) {
			return isReadWritePermittedBySharing($module, $tabid, $actionid, $record_id);
		} elseif ($actionid == 2) {
			return 'no';
		} else {
			return 'yes';
		}
	} else {
		return 'yes';
	}
	return 'no';
}

/** Function to check if the currently logged in user has Read Access due to Sharing for the specified record
 * @param $module -- Module Name:: Type varchar
 * @param $actionid -- Action Id:: Type integer
 * @param $recordid -- Record Id:: Type integer
 * @param $tabid -- Tab Id:: Type integer
 * @returns yes or no. If Yes means this action is allowed for the currently logged in user. If no means this action is not allowed for the currently logged in user
 */
function isReadPermittedBySharing($module, $tabid, $actionid, $record_id)
{

	\App\Log::trace("Entering isReadPermittedBySharing(" . $module . "," . $tabid . "," . $actionid . "," . $record_id . ") method ...");
	$adb = PearDatabase::getInstance();
	$current_user = vglobal('current_user');
	require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
	$ownertype = '';
	$ownerid = '';
	$sharePer = 'no';

	$sharingModuleList = \App\Module::getSharingModuleList();
	if (!in_array($module, $sharingModuleList)) {
		$sharePer = 'no';
		return $sharePer;
	}

	$recordOwnerArr = getRecordOwnerId($record_id);
	foreach ($recordOwnerArr as $type => $id) {
		$ownertype = $type;
		$ownerid = $id;
	}

	$varname = $module . "_share_read_permission";
	$read_per_arr = $$varname;
	if ($ownertype == 'Users') {
		//Checking the Read Sharing Permission Array in Role Users
		$read_role_per = $read_per_arr['ROLE'];
		foreach ($read_role_per as $roleid => $userids) {
			if (in_array($ownerid, $userids)) {
				$sharePer = 'yes';
				\App\Log::trace("Exiting isReadPermittedBySharing method ...");
				return $sharePer;
			}
		}

		//Checking the Read Sharing Permission Array in Groups Users
		$read_grp_per = $read_per_arr['GROUP'];
		foreach ($read_grp_per as $grpid => $userids) {
			if (in_array($ownerid, $userids)) {
				$sharePer = 'yes';
				\App\Log::trace("Exiting isReadPermittedBySharing method ...");
				return $sharePer;
			}
		}
	} elseif ($ownertype == 'Groups') {
		$read_grp_per = $read_per_arr['GROUP'];
		if (array_key_exists($ownerid, $read_grp_per)) {
			$sharePer = 'yes';
			\App\Log::trace("Exiting isReadPermittedBySharing method ...");
			return $sharePer;
		}
	}

	//Checking for the Related Sharing Permission
	$relatedModuleArray = $related_module_share[$tabid];
	if (is_array($relatedModuleArray)) {
		foreach ($relatedModuleArray as $parModId) {
			$parRecordOwner = App\PrivilegeUtil::getParentRecordOwner($tabid, $parModId, $record_id);
			if (sizeof($parRecordOwner) > 0) {
				$parModName = \App\Module::getModuleName($parModId);
				$rel_var = $parModName . "_" . $module . "_share_read_permission";
				$read_related_per_arr = $$rel_var;
				$rel_owner_type = '';
				$rel_owner_id = '';
				foreach ($parRecordOwner as $rel_type => $rel_id) {
					$rel_owner_type = $rel_type;
					$rel_owner_id = $rel_id;
				}
				if ($rel_owner_type == 'Users') {
					//Checking in Role Users
					$read_related_role_per = $read_related_per_arr['ROLE'];
					foreach ($read_related_role_per as $roleid => $userids) {
						if (in_array($rel_owner_id, $userids)) {
							$sharePer = 'yes';
							\App\Log::trace("Exiting isReadPermittedBySharing method ...");
							return $sharePer;
						}
					}
					//Checking in Group Users
					$read_related_grp_per = $read_related_per_arr['GROUP'];
					foreach ($read_related_grp_per as $grpid => $userids) {
						if (in_array($rel_owner_id, $userids)) {
							$sharePer = 'yes';
							\App\Log::trace("Exiting isReadPermittedBySharing method ...");
							return $sharePer;
						}
					}
				} elseif ($rel_owner_type == 'Groups') {
					$read_related_grp_per = $read_related_per_arr['GROUP'];
					if (array_key_exists($rel_owner_id, $read_related_grp_per)) {
						$sharePer = 'yes';
						\App\Log::trace("Exiting isReadPermittedBySharing method ...");
						return $sharePer;
					}
				}
			}
		}
	}
	\App\Log::trace("Exiting isReadPermittedBySharing method ...");
	return $sharePer;
}

/** Function to check if the currently logged in user has Write Access due to Sharing for the specified record
 * @param $module -- Module Name:: Type varchar
 * @param $actionid -- Action Id:: Type integer
 * @param $recordid -- Record Id:: Type integer
 * @param $tabid -- Tab Id:: Type integer
 * @returns yes or no. If Yes means this action is allowed for the currently logged in user. If no means this action is not allowed for the currently logged in user
 */
function isReadWritePermittedBySharing($module, $tabid, $actionid, $record_id)
{

	\App\Log::trace("Entering isReadWritePermittedBySharing(" . $module . "," . $tabid . "," . $actionid . "," . $record_id . ") method ...");
	$adb = PearDatabase::getInstance();
	$current_user = vglobal('current_user');
	require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
	$ownertype = '';
	$ownerid = '';
	$sharePer = 'no';

	$sharingModuleList = \App\Module::getSharingModuleList();
	if (!in_array($module, $sharingModuleList)) {
		$sharePer = 'no';
		return $sharePer;
	}

	$recordOwnerArr = getRecordOwnerId($record_id);
	foreach ($recordOwnerArr as $type => $id) {
		$ownertype = $type;
		$ownerid = $id;
	}

	$varname = $module . "_share_write_permission";
	$write_per_arr = $$varname;

	if ($ownertype == 'Users') {
		//Checking the Write Sharing Permission Array in Role Users
		$write_role_per = $write_per_arr['ROLE'];
		foreach ($write_role_per as $roleid => $userids) {
			if (in_array($ownerid, $userids)) {
				$sharePer = 'yes';
				\App\Log::trace("Exiting isReadWritePermittedBySharing method ...");
				return $sharePer;
			}
		}
		//Checking the Write Sharing Permission Array in Groups Users
		$write_grp_per = $write_per_arr['GROUP'];
		foreach ($write_grp_per as $grpid => $userids) {
			if (in_array($ownerid, $userids)) {
				$sharePer = 'yes';
				\App\Log::trace("Exiting isReadWritePermittedBySharing method ...");
				return $sharePer;
			}
		}
	} elseif ($ownertype == 'Groups') {
		$write_grp_per = $write_per_arr['GROUP'];
		if (isset($write_grp_per[$ownerid])) {
			$sharePer = 'yes';
			\App\Log::trace("Exiting isReadWritePermittedBySharing method ...");
			return $sharePer;
		}
	}
	//Checking for the Related Sharing Permission
	$relatedModuleArray = $related_module_share[$tabid];
	if (is_array($relatedModuleArray)) {
		foreach ($relatedModuleArray as $parModId) {
			$parRecordOwner = App\PrivilegeUtil::getParentRecordOwner($tabid, $parModId, $record_id);
			if (sizeof($parRecordOwner) > 0) {
				$parModName = \App\Module::getModuleName($parModId);
				$rel_var = $parModName . "_" . $module . "_share_write_permission";
				$write_related_per_arr = $$rel_var;
				$rel_owner_type = '';
				$rel_owner_id = '';
				foreach ($parRecordOwner as $rel_type => $rel_id) {
					$rel_owner_type = $rel_type;
					$rel_owner_id = $rel_id;
				}
				if ($rel_owner_type == 'Users') {
					//Checking in Role Users
					$write_related_role_per = $write_related_per_arr['ROLE'];
					foreach ($write_related_role_per as $roleid => $userids) {
						if (in_array($rel_owner_id, $userids)) {
							$sharePer = 'yes';
							\App\Log::trace("Exiting isReadWritePermittedBySharing method ...");
							return $sharePer;
						}
					}
					//Checking in Group Users
					$write_related_grp_per = $write_related_per_arr['GROUP'];
					foreach ($write_related_grp_per as $grpid => $userids) {
						if (in_array($rel_owner_id, $userids)) {
							$sharePer = 'yes';
							\App\Log::trace("Exiting isReadWritePermittedBySharing method ...");
							return $sharePer;
						}
					}
				} elseif ($rel_owner_type == 'Groups') {
					$write_related_grp_per = $write_related_per_arr['GROUP'];
					if (array_key_exists($rel_owner_id, $write_related_grp_per)) {
						$sharePer = 'yes';
						\App\Log::trace("Exiting isReadWritePermittedBySharing method ...");
						return $sharePer;
					}
				}
			}
		}
	}

	\App\Log::trace("Exiting isReadWritePermittedBySharing method ...");
	return $sharePer;
}

/** Function to get the Profile Global Information for the specified vtiger_profileid
 * @param $profileid -- Profile Id:: Type integer
 * @returns Profile Gloabal Permission Array in the following format:
 * $profileGloblaPermisson=Array($viewall_actionid=>permission, $editall_actionid=>permission)
 */
function getProfileGlobalPermission($profileid)
{

	\App\Log::trace("Entering getProfileGlobalPermission(" . $profileid . ") method ...");
	$adb = PearDatabase::getInstance();
	$sql = "select * from vtiger_profile2globalpermissions where profileid=?";
	$result = $adb->pquery($sql, array($profileid));
	$num_rows = $adb->num_rows($result);

	for ($i = 0; $i < $num_rows; $i++) {
		$act_id = $adb->query_result($result, $i, "globalactionid");
		$per_id = $adb->query_result($result, $i, "globalactionpermission");
		$copy[$act_id] = $per_id;
	}

	\App\Log::trace("Exiting getProfileGlobalPermission method ...");
	return $copy;
}

/** Function to get the Profile Tab Permissions for the specified vtiger_profileid
 * @param $profileid -- Profile Id:: Type integer
 * @returns Profile Tabs Permission Array in the following format:
 * $profileTabPermisson=Array($tabid1=>permission, $tabid2=>permission,........., $tabidn=>permission)
 */
function getProfileTabsPermission($profileid)
{

	\App\Log::trace("Entering getProfileTabsPermission(" . $profileid . ") method ...");
	$adb = PearDatabase::getInstance();
	$sql = "select * from vtiger_profile2tab where profileid=?";
	$result = $adb->pquery($sql, array($profileid));
	$num_rows = $adb->num_rows($result);

	$copy = [];
	for ($i = 0; $i < $num_rows; $i++) {
		$tab_id = $adb->query_result($result, $i, "tabid");
		$per_id = $adb->query_result($result, $i, "permissions");
		$copy[$tab_id] = $per_id;
	}

	\App\Log::trace("Exiting getProfileTabsPermission method ...");
	return $copy;
}

/** Function to get the Profile Action Permissions for the specified vtiger_profileid
 * @param $profileid -- Profile Id:: Type integer
 * @returns Profile Tabs Action Permission Array in the following format:
 *    $tabActionPermission = Array($tabid1=>Array(actionid1=>permission, actionid2=>permission,...,actionidn=>permission),
 *                        $tabid2=>Array(actionid1=>permission, actionid2=>permission,...,actionidn=>permission),
 *                                |
 *                        $tabidn=>Array(actionid1=>permission, actionid2=>permission,...,actionidn=>permission))
 */
function getProfileActionPermission($profileid)
{

	\App\Log::trace("Entering getProfileActionPermission(" . $profileid . ") method ...");
	$adb = PearDatabase::getInstance();
	$check = [];
	$temp_tabid = [];
	$sql1 = "select * from vtiger_profile2standardpermissions where profileid=?";
	$result1 = $adb->pquery($sql1, array($profileid));
	$num_rows1 = $adb->num_rows($result1);
	for ($i = 0; $i < $num_rows1; $i++) {
		$tab_id = $adb->query_result($result1, $i, 'tabid');
		if (!in_array($tab_id, $temp_tabid)) {
			$temp_tabid[] = $tab_id;
			$access = [];
		}

		$action_id = $adb->query_result($result1, $i, 'operation');
		$per_id = $adb->query_result($result1, $i, 'permissions');
		$access[$action_id] = $per_id;
		$check[$tab_id] = $access;
	}


	\App\Log::trace("Exiting getProfileActionPermission method ...");
	return $check;
}

/** Function to get the Standard and Utility Profile Action Permissions for the specified vtiger_profileid
 * @param $profileid -- Profile Id:: Type integer
 * @returns Profile Tabs Action Permission Array in the following format:
 *    $tabActionPermission = Array($tabid1=>Array(actionid1=>permission, actionid2=>permission,...,actionidn=>permission),
 *                        $tabid2=>Array(actionid1=>permission, actionid2=>permission,...,actionidn=>permission),
 *                                |
 *                        $tabidn=>Array(actionid1=>permission, actionid2=>permission,...,actionidn=>permission))
 */
function getProfileAllActionPermission($profileid)
{

	\App\Log::trace("Entering getProfileAllActionPermission(" . $profileid . ") method ...");
	$adb = PearDatabase::getInstance();
	$actionArr = getProfileActionPermission($profileid);
	$utilArr = getTabsUtilityActionPermission($profileid);
	foreach ($utilArr as $tabid => $act_arr) {
		$act_tab_arr = $actionArr[$tabid];
		foreach ($act_arr as $utilid => $util_perr) {
			$act_tab_arr[$utilid] = $util_perr;
		}
		$actionArr[$tabid] = $act_tab_arr;
	}
	\App\Log::trace("Exiting getProfileAllActionPermission method ...");
	return $actionArr;
}

/** Function to get all  the vtiger_role information
 * @returns $allRoleDetailArray-- Array will contain the details of all the vtiger_roles. RoleId will be the key:: Type array
 */
function getAllRoleDetails()
{

	\App\Log::trace('Entering getAllRoleDetails() method ...');
	$adb = PearDatabase::getInstance();
	$role_det = [];
	$query = "select * from vtiger_role";
	$result = $adb->pquery($query, []);
	$num_rows = $adb->num_rows($result);
	for ($i = 0; $i < $num_rows; $i++) {
		$each_role_det = [];
		$roleid = $adb->query_result($result, $i, 'roleid');
		$rolename = $adb->query_result($result, $i, 'rolename');
		$roledepth = $adb->query_result($result, $i, 'depth');
		$sub_roledepth = $roledepth + 1;
		$parentrole = $adb->query_result($result, $i, 'parentrole');
		$sub_role = '';

		//getting the immediate subordinates
		$query1 = "select * from vtiger_role where parentrole like ? and depth=?";
		$res1 = $adb->pquery($query1, array($parentrole . "::%", $sub_roledepth));
		$num_roles = $adb->num_rows($res1);
		if ($num_roles > 0) {
			for ($j = 0; $j < $num_roles; $j++) {
				if ($j == 0) {
					$sub_role .= $adb->query_result($res1, $j, 'roleid');
				} else {
					$sub_role .= ',' . $adb->query_result($res1, $j, 'roleid');
				}
			}
		}


		$each_role_det[] = $rolename;
		$each_role_det[] = $roledepth;
		$each_role_det[] = $sub_role;
		$role_det[$roleid] = $each_role_det;
	}
	\App\Log::trace('Exiting getAllRoleDetails method ...');
	return $role_det;
}

/** Function to get the vtiger_role related vtiger_users
 * @param $roleid -- RoleId :: Type varchar
 * @returns $roleUsers-- Role Related User Array in the following format:
 *       $roleUsers=Array($userId1=>$userName,$userId2=>$userName,........,$userIdn=>$userName));
 */
function getRoleUsers($roleId)
{

	\App\Log::trace('Entering getRoleUsers(' . $roleId . ') method ...');

	$roleRelatedUsers = Vtiger_Cache::get('getRoleUsers', $roleId);
	if ($roleRelatedUsers !== false) {
		return $roleRelatedUsers;
	}

	$adb = PearDatabase::getInstance();
	$query = 'select vtiger_user2role.*,vtiger_users.* from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid where roleid=?';
	$result = $adb->pquery($query, array($roleId));
	$num_rows = $adb->num_rows($result);
	$roleRelatedUsers = [];
	for ($i = 0; $i < $num_rows; $i++) {
		$roleRelatedUsers[$adb->query_result($result, $i, 'userid')] = \vtlib\Deprecated::getFullNameFromQResult($result, $i, 'Users');
	}

	Vtiger_Cache::set('getRoleUsers', $roleId, $roleRelatedUsers);
	\App\Log::trace('Exiting getRoleUsers method ...');
	return $roleRelatedUsers;
}

/** Function to get the vtiger_role related user ids
 * @param $roleid -- RoleId :: Type varchar
 * @returns $roleUserIds-- Role Related User Array in the following format:
 *       $roleUserIds=Array($userId1,$userId2,........,$userIdn);
 */
function getRoleUserIds($roleId)
{
	return \App\PrivilegeUtil::getUsersByRole($roleId);
}

/** Function to get the vtiger_role and subordinate vtiger_users
 * @param $roleid -- RoleId :: Type varchar
 * @returns $roleSubUsers-- Role and Subordinates Related Users Array in the following format:
 *       $roleSubUsers=Array($userId1=>$userName,$userId2=>$userName,........,$userIdn=>$userName));
 */
function getRoleAndSubordinateUsers($roleId)
{

	\App\Log::trace("Entering getRoleAndSubordinateUsers(" . $roleId . ") method ...");
	$adb = PearDatabase::getInstance();
	$roleInfoArr = \App\PrivilegeUtil::getRoleDetail($roleId);
	$parentRole = $roleInfoArr['parentrole'];
	$query = "select vtiger_user2role.*,vtiger_users.user_name from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like ?";
	$result = $adb->pquery($query, array($parentRole . "%"));
	$num_rows = $adb->num_rows($result);
	$roleRelatedUsers = [];
	for ($i = 0; $i < $num_rows; $i++) {
		$roleRelatedUsers[$adb->query_result($result, $i, 'userid')] = $adb->query_result($result, $i, 'user_name');
	}
	\App\Log::trace("Exiting getRoleAndSubordinateUsers method ...");
	return $roleRelatedUsers;
}

/** Function to get the vtiger_role and subordinate Information for the specified vtiger_roleId
 * @param $roleid -- RoleId :: Type varchar
 * @returns $roleSubInfo-- Role and Subordinates Information array in the following format:
 *       $roleSubInfo=Array($roleId1=>Array($rolename,$parentrole,$roledepth,$immediateParent), $roleId2=>Array($rolename,$parentrole,$roledepth,$immediateParent),.....);
 */
function getRoleAndSubordinatesInformation($roleId)
{

	\App\Log::trace("Entering getRoleAndSubordinatesInformation(" . $roleId . ") method ...");
	$adb = PearDatabase::getInstance();
	static $roleInfoCache = [];
	if (!empty($roleInfoCache[$roleId])) {
		return $roleInfoCache[$roleId];
	}
	$roleDetails = \App\PrivilegeUtil::getRoleDetail($roleId);
	$roleParentSeq = $roleDetails['parentrole'];

	$query = "select * from vtiger_role where parentrole like ? order by parentrole asc";
	$result = $adb->pquery($query, array($roleParentSeq . "%"));
	$num_rows = $adb->num_rows($result);
	$roleInfo = [];
	for ($i = 0; $i < $num_rows; $i++) {
		$roleid = $adb->query_result($result, $i, 'roleid');
		$rolename = $adb->query_result($result, $i, 'rolename');
		$roledepth = $adb->query_result($result, $i, 'depth');
		$parentrole = $adb->query_result($result, $i, 'parentrole');
		$roleDet = [];
		$roleDet[] = $rolename;
		$roleDet[] = $parentrole;
		$roleDet[] = $roledepth;
		$roleInfo[$roleid] = $roleDet;
	}
	$roleInfoCache[$roleId] = $roleInfo;
	\App\Log::trace("Exiting getRoleAndSubordinatesInformation method ...");
	return $roleInfo;
}

/** Function to get the vtiger_role and subordinate vtiger_role ids
 * @param $roleid -- RoleId :: Type varchar
 * @returns $roleSubRoleIds-- Role and Subordinates RoleIds in an Array in the following format:
 *       $roleSubRoleIds=Array($roleId1,$roleId2,........,$roleIdn);
 */
function getRoleAndSubordinatesRoleIds($roleId)
{

	\App\Log::trace("Entering getRoleAndSubordinatesRoleIds(" . $roleId . ") method ...");
	$adb = PearDatabase::getInstance();
	$roleDetails = \App\PrivilegeUtil::getRoleDetail($roleId);
	$roleParentSeq = $roleDetails['parentrole'];

	$query = "select * from vtiger_role where parentrole like ? order by parentrole asc";
	$result = $adb->pquery($query, array($roleParentSeq . "%"));
	$num_rows = $adb->num_rows($result);
	$roleInfo = [];
	for ($i = 0; $i < $num_rows; $i++) {
		$roleid = $adb->query_result($result, $i, 'roleid');
		$roleInfo[] = $roleid;
	}
	\App\Log::trace("Exiting getRoleAndSubordinatesRoleIds method ...");
	return $roleInfo;
}

/** Function to delete the vtiger_role related sharing rules
 * @param $roleid -- RoleId :: Type varchar
 */
function deleteRoleRelatedSharingRules($roleId)
{

	\App\Log::trace('Entering deleteRoleRelatedSharingRules(' . $roleId . ') method ...');
	$adb = PearDatabase::getInstance();
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
		$params = array($grpId);
		if (sizeof($colNameArr) > 1) {
			$query .= " or " . $colNameArr[1] . "=?";
			array_push($params, $grpId);
		}

		$result = $adb->pquery($query, $params);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$shareid = $adb->query_result($result, $i, 'shareid');
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


	foreach ($dataShareTableColArr as $tablename => $colname) {
		$colNameArr = explode('::', $colname);
		$query = sprintf("SELECT shareid FROM %s WHERE %s = ?", $tablename, $colNameArr[0]);
		$params = array($grpId);
		if (sizeof($colNameArr) > 1) {
			$query .= " or " . $colNameArr[1] . "=?";
			array_push($params, $grpId);
		}

		$result = $adb->pquery($query, $params);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$shareid = $adb->query_result($result, $i, 'shareid');
			deleteSharingRule($shareid);
		}
	}
	\App\Log::trace('Exiting deleteGroupRelatedSharingRules method ...');
}

/** Function to get userid and username of all vtiger_users
 * @returns $userArray -- User Array in the following format:
 * $userArray=Array($userid1=>$username, $userid2=>$username,............,$useridn=>$username);
 */
function getAllUserName()
{

	\App\Log::trace("Entering getAllUserName() method ...");
	$adb = PearDatabase::getInstance();
	$query = "select * from vtiger_users where deleted=0";
	$result = $adb->pquery($query, []);
	$num_rows = $adb->num_rows($result);
	$user_details = [];
	for ($i = 0; $i < $num_rows; $i++) {
		$userid = $adb->query_result($result, $i, 'id');
		$username = \vtlib\Deprecated::getFullNameFromQResult($result, $i, 'Users');
		$user_details[$userid] = $username;
	}
	\App\Log::trace("Exiting getAllUserName method ...");
	return $user_details;
}

/** Function to get groupid and groupname of all vtiger_groups
 * @returns $grpArray -- Group Array in the following format:
 * $grpArray=Array($grpid1=>$grpname, $grpid2=>$grpname,............,$grpidn=>$grpname);
 */
function getAllGroupName()
{

	\App\Log::trace("Entering getAllGroupName() method ...");
	$adb = PearDatabase::getInstance();
	$query = "select * from vtiger_groups";
	$result = $adb->pquery($query, []);
	$num_rows = $adb->num_rows($result);
	$group_details = [];
	for ($i = 0; $i < $num_rows; $i++) {
		$grpid = $adb->query_result($result, $i, 'groupid');
		$grpname = $adb->query_result($result, $i, 'groupname');
		$group_details[$grpid] = $grpname;
	}
	\App\Log::trace("Exiting getAllGroupName method ...");
	return $group_details;
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
	$res = $adb->pquery($query2, array($shareid));
	$typestr = $adb->query_result($res, 0, 'relationtype');
	$tabname = getDSTableNameForType($typestr);
	$query3 = "delete from $tabname where shareid=?";
	$adb->pquery($query3, array($shareid));
	$query4 = "delete from vtiger_datashare_module_rel where shareid=?";
	$adb->pquery($query4, array($shareid));

	//deleting the releated module sharing permission
	$query5 = "delete from vtiger_datashare_relatedmodule_permission where shareid=?";
	$adb->pquery($query5, array($shareid));
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

/** To retreive the global permission of the specifed user from the various vtiger_profiles associated with the user
 * @param $userid -- The User Id:: Type Integer
 * @returns  user global permission  array in the following format:
 *     $gloabalPerrArray=(view all action id=>permission,
  edit all action id=>permission)							);
 */
function getCombinedUserGlobalPermissions($userId)
{

	\App\Log::trace("Entering getCombinedUserGlobalPermissions(" . $userId . ") method ...");
	$adb = PearDatabase::getInstance();
	$profArr = \App\PrivilegeUtil::getProfilesByUser($userId);
	$no_of_profiles = sizeof($profArr);
	$userGlobalPerrArr = [];

	$userGlobalPerrArr = getProfileGlobalPermission($profArr[0]);
	if ($no_of_profiles != 1) {
		for ($i = 1; $i < $no_of_profiles; $i++) {
			$tempUserGlobalPerrArr = getProfileGlobalPermission($profArr[$i]);

			foreach ($userGlobalPerrArr as $globalActionId => $globalActionPermission) {
				if ($globalActionPermission == 1) {
					$now_permission = $tempUserGlobalPerrArr[$globalActionId];
					if ($now_permission == 0) {
						$userGlobalPerrArr[$globalActionId] = $now_permission;
					}
				}
			}
		}
	}

	\App\Log::trace("Exiting getCombinedUserGlobalPermissions method ...");
	return $userGlobalPerrArr;
}

/** To retreive the vtiger_tab permissions of the specifed user from the various vtiger_profiles associated with the user
 * @param $userid -- The User Id:: Type Integer
 * @returns  user global permission  array in the following format:
 *     $tabPerrArray=(tabid1=>permission,
 * 			   tabid2=>permission)							);
 */
function getCombinedUserTabsPermissions($userId)
{

	\App\Log::trace("Entering getCombinedUserTabsPermissions(" . $userId . ") method ...");
	$adb = PearDatabase::getInstance();
	$profArr = \App\PrivilegeUtil::getProfilesByUser($userId);
	$no_of_profiles = sizeof($profArr);
	$userTabPerrArr = [];

	$userTabPerrArr = getProfileTabsPermission($profArr[0]);
	if ($no_of_profiles != 1) {
		for ($i = 1; $i < $no_of_profiles; $i++) {
			$tempUserTabPerrArr = getProfileTabsPermission($profArr[$i]);

			foreach ($userTabPerrArr as $tabId => $tabPermission) {
				if ($tabPermission == 1) {
					$now_permission = $tempUserTabPerrArr[$tabId];
					if ($now_permission == 0) {
						$userTabPerrArr[$tabId] = $now_permission;
					}
				}
			}
		}
	}

	$homeTabid = \App\Module::getModuleId('Home');
	if (!array_key_exists($homeTabid, $userTabPerrArr)) {
		$userTabPerrArr[$homeTabid] = 0;
	}
	\App\Log::trace("Exiting getCombinedUserTabsPermissions method ...");
	return $userTabPerrArr;
}

/** To retreive the vtiger_tab acion permissions of the specifed user from the various vtiger_profiles associated with the user
 * @param $userid -- The User Id:: Type Integer
 * @returns  user global permission  array in the following format:
 *     $actionPerrArray=(tabid1=>permission,
 * 			   tabid2=>permission);
 */
function getCombinedUserActionPermissions($userId)
{

	\App\Log::trace("Entering getCombinedUserActionPermissions(" . $userId . ") method ...");
	$adb = PearDatabase::getInstance();
	$profArr = \App\PrivilegeUtil::getProfilesByUser($userId);
	$no_of_profiles = sizeof($profArr);
	$actionPerrArr = [];

	$actionPerrArr = getProfileAllActionPermission($profArr[0]);
	if ($no_of_profiles != 1) {
		for ($i = 1; $i < $no_of_profiles; $i++) {
			$tempActionPerrArr = getProfileAllActionPermission($profArr[$i]);

			foreach ($actionPerrArr as $tabId => $perArr) {
				foreach ($perArr as $actionid => $per) {
					if ($per == 1) {
						$now_permission = $tempActionPerrArr[$tabId][$actionid];
						if ($now_permission == 0 && $now_permission != "") {
							$actionPerrArr[$tabId][$actionid] = $now_permission;
						}
					}
				}
			}
		}
	}
	\App\Log::trace("Exiting getCombinedUserActionPermissions method ...");
	return $actionPerrArr;
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
	$adb = PearDatabase::getInstance();
	$subRoleAndUsers = [];
	$subordinateRoles = \App\PrivilegeUtil::getRoleSubordinates($roleId);
	foreach ($subordinateRoles as $subRoleId) {
		$userArray = getRoleUsers($subRoleId);
		$subRoleAndUsers[$subRoleId] = $userArray;
	}
	\App\Log::trace("Exiting getSubordinateRoleAndUsers method ...");
	return $subRoleAndUsers;
}

function getCurrentUserGroupList()
{

	\App\Log::trace("Entering getCurrentUserGroupList() method ...");
	$current_user = vglobal('current_user');
	require('user_privileges/user_privileges_' . $current_user->id . '.php');
	$grpList = [];
	if (sizeof($current_user_groups) > 0) {
		$i = 0;
		foreach ($current_user_groups as $grpid) {
			array_push($grpList, $grpid);
			$i++;
		}
	}
	\App\Log::trace("Exiting getCurrentUserGroupList method ...");
	return $grpList;
}

function getWriteSharingGroupsList($module)
{

	\App\Log::trace("Entering getWriteSharingGroupsList(" . $module . ") method ...");
	$adb = PearDatabase::getInstance();
	$current_user = vglobal('current_user');
	$grp_array = [];
	$tabid = \App\Module::getModuleId($module);
	$query = "select sharedgroupid from vtiger_tmp_write_group_sharing_per where userid=? and tabid=?";
	$result = $adb->pquery($query, array($current_user->id, $tabid));
	$num_rows = $adb->num_rows($result);
	for ($i = 0; $i < $num_rows; $i++) {
		$grp_id = $adb->query_result($result, $i, 'sharedgroupid');
		$grp_array[] = $grp_id;
	}
	$shareGrpList = constructList($grp_array, 'INTEGER');
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

function get_current_user_access_groups($module)
{

	\App\Log::trace("Entering get_current_user_access_groups(" . $module . ") method ...");
	$adb = PearDatabase::getInstance();
	$current_user_group_list = getCurrentUserGroupList();
	$sharing_write_group_list = getWriteSharingGroupsList($module);
	$query = "select groupname,groupid from vtiger_groups";
	$params = [];
	if (count($current_user_group_list) > 0 && count($sharing_write_group_list) > 0) {
		$query .= sprintf(" WHERE (groupid in (%s) || groupid IN (%s))", generateQuestionMarks($current_user_group_list), generateQuestionMarks($sharing_write_group_list));
		array_push($params, $current_user_group_list, $sharing_write_group_list);
		$result = $adb->pquery($query, $params);
	} elseif (count($current_user_group_list) > 0) {
		$query .= sprintf(" WHERE groupid IN (%s)", generateQuestionMarks($current_user_group_list));
		array_push($params, $current_user_group_list);
		$result = $adb->pquery($query, $params);
	} elseif (count($sharing_write_group_list) > 0) {
		$query .= sprintf(" WHERE groupid IN (%s)", generateQuestionMarks($sharing_write_group_list));
		array_push($params, $sharing_write_group_list);
		$result = $adb->pquery($query, $params);
	}
	\App\Log::trace("Exiting get_current_user_access_groups method ...");
	return $result;
}

/** Function to get the permitted module name Array with presence as 0
 * @returns permitted module name Array :: Type Array
 *
 */
function getPermittedModuleNames()
{

	\App\Log::trace("Entering getPermittedModuleNames() method ...");
	$current_user = vglobal('current_user');
	$permittedModules = [];
	require('user_privileges/user_privileges_' . $current_user->id . '.php');
	include('user_privileges/tabdata.php');

	if ($is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
		foreach ($tab_seq_array as $tabid => $seq_value) {
			if ($seq_value === 0 && $profileTabsPermission[$tabid] === 0) {
				$permittedModules[] = \App\Module::getModuleName($tabid);
			}
		}
	} else {
		foreach ($tab_seq_array as $tabid => $seq_value) {
			if ($seq_value === 0) {
				$permittedModules[] = \App\Module::getModuleName($tabid);
			}
		}
	}
	\App\Log::trace("Exiting getPermittedModuleNames method ...");
	return $permittedModules;
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
	$adb = PearDatabase::getInstance();
	require_once('modules/Users/CreateUserPrivilegeFile.php');
	$query = "select id from vtiger_users where deleted=0";
	$result = $adb->pquery($query, []);
	$num_rows = $adb->num_rows($result);
	for ($i = 0; $i < $num_rows; $i++) {
		$id = $adb->query_result($result, $i, 'id');
		createUserPrivilegesfile($id);
		createUserSharingPrivilegesfile($id);
	}
	\App\Log::trace("Exiting RecalculateSharingRules method ...");
}

/**
 *
 * @param String $module - module name for which query needs to be generated.
 * @param Users $user - user for which query needs to be generated.
 * @return String Access control Query for the user.
 */
function getNonAdminAccessControlQuery($module, $user, $scope = '')
{
	$instance = CRMEntity::getInstance($module);
	return $instance->getNonAdminAccessControlQuery($module, $user, $scope);
}
