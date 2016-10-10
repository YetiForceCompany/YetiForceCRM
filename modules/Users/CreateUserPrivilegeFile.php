<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('config/config.php');
require_once('modules/Users/Users.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/utils/utils.php');
require_once('include/utils/GetUserGroups.php');
require_once('include/utils/GetGroupUsers.php');

/** Creates a file with all the user, user-role,user-profile, user-groups informations 
 * @param $userid -- user id:: Type integer
 * @returns user_privileges_userid file under the user_privileges directory
 */
function createUserPrivilegesfile($userid)
{
	$handle = @fopen(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges/user_privileges_' . $userid . '.php', "w+");

	if ($handle) {
		$newbuf = '';
		$newbuf .="<?php\n";
		$user_focus = CRMEntity::getInstance('Users');
		$user_focus->retrieve_entity_info($userid, 'Users');
		$userInfo = [];
		$user_focus->column_fields["id"] = '';
		$user_focus->id = $userid;
		foreach ($user_focus->column_fields as $field => $value_iter) {
			if (isset($user_focus->$field)) {
				$userInfo[$field] = $user_focus->$field;
			}
		}

		if ($user_focus->is_admin == 'on') {
			$newbuf .= "\$is_admin=true;\n";
			$newbuf .= "\$user_info=" . constructSingleStringKeyValueArray($userInfo) . ";\n";
		} else {
			$newbuf .= "\$is_admin=false;\n";

			$globalPermissionArr = getCombinedUserGlobalPermissions($userid);
			$tabsPermissionArr = getCombinedUserTabsPermissions($userid);
			$actionPermissionArr = getCombinedUserActionPermissions($userid);
			$user_role = fetchUserRole($userid);
			$user_role_info = getRoleInformation($user_role);
			$user_role_parent = $user_role_info['parentrole'];
			$userGroupFocus = new GetUserGroups();
			$userGroupFocus->getAllUserGroups($userid);
			$subRoles = getRoleSubordinates($user_role);
			$subRoleAndUsers = getSubordinateRoleAndUsers($user_role);
			$def_org_share = getDefaultSharingAction();
			$parentRoles = getParentRole($user_role);

			$newbuf .= "\$current_user_roles='" . $user_role . "';\n";
			$newbuf .= "\$current_user_parent_role_seq='" . $user_role_parent . "';\n";
			$newbuf .= "\$current_user_profiles=" . constructSingleArray(getUserProfile($userid)) . ";\n";
			$newbuf .= "\$profileGlobalPermission=" . constructArray($globalPermissionArr) . ";\n";
			$newbuf .= "\$profileTabsPermission=" . constructArray($tabsPermissionArr) . ";\n";
			$newbuf .= "\$profileActionPermission=" . constructTwoDimensionalArray($actionPermissionArr) . ";\n";
			$newbuf .= "\$current_user_groups=" . constructSingleArray($userGroupFocus->user_groups) . ";\n";
			$newbuf .= "\$subordinate_roles=" . constructSingleCharArray($subRoles) . ";\n";
			$newbuf .= "\$parent_roles=" . constructSingleCharArray($parentRoles) . ";\n";
			$newbuf .= "\$subordinate_roles_users=" . constructTwoDimensionalCharIntSingleArray($subRoleAndUsers) . ";\n";
			$newbuf .= "\$user_info=" . constructSingleStringKeyValueArray($userInfo) . ";\n";
		}
		fputs($handle, $newbuf);
		fclose($handle);
	}
}

/** Creates a file with all the organization default sharing permissions and custom sharing permissins specific for the specified user. In this file the information of the other users whose data is shared with the specified user is stored.   
 * @param $userid -- user id:: Type integer
 * @returns sharing_privileges_userid file under the user_privileges directory
 */
function createUserSharingPrivilegesfile($userid)
{
	\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/user_privileges_' . $userid . '.php');
	require('user_privileges/user_privileges_' . $userid . '.php');
	$handle = @fopen(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges/sharing_privileges_' . $userid . '.php', "w+");

	if ($handle) {
		$newbuf = "<?php\n";
		$user_focus = CRMEntity::getInstance('Users');
		$user_focus->retrieve_entity_info($userid, 'Users');
		if ($user_focus->is_admin == 'on') {
			fputs($handle, $newbuf);
			fclose($handle);
			return;
		} else {
			$sharingPrivileges = [];
			//Constructig the Default Org Share Array
			$def_org_share = \includes\PrivilegesUtils::getAllDefaultSharingAction();
			$newbuf .= "\$defaultOrgSharingPermission=" . constructArray($def_org_share) . ";\n";
			$sharingPrivileges['defOrgShare'] = $def_org_share;

			$relatedModuleShare = \includes\PrivilegesUtils::getDatashareRelatedModules();
			$newbuf .= "\$related_module_share=" . constructTwoDimensionalValueArray($relatedModuleShare) . ";\n";
			$sharingPrivileges['relatedModuleShare'] = $relatedModuleShare;
			//Constructing Account Sharing Rules
			$account_share_per_array = getUserModuleSharingObjects('Accounts', $userid, $def_org_share, $current_user_roles, $parent_roles, $current_user_groups);
			$account_share_read_per = $account_share_per_array['read'];
			$account_share_write_per = $account_share_per_array['write'];
			$account_sharingrule_members = $account_share_per_array['sharingrules'];
			/* echo '<pre>';
			  print_r($account_share_read_per['GROUP']);
			  echo '</pre>'; */
			$newbuf .= "\$Accounts_share_read_permission=array('ROLE'=>" . constructTwoDimensionalCharIntSingleValueArray($account_share_read_per['ROLE']) . ",'GROUP'=>" . constructTwoDimensionalValueArray($account_share_read_per['GROUP']) . ");\n";
			$newbuf .= "\$Accounts_share_write_permission=array('ROLE'=>" . constructTwoDimensionalCharIntSingleValueArray($account_share_write_per['ROLE']) . ",'GROUP'=>" . constructTwoDimensionalValueArray($account_share_write_per['GROUP']) . ");\n";
			$sharingPrivileges['permission']['Accounts'] = ['read' => $account_share_read_per, 'write' => $account_share_write_per];
			//Constructing Contact Sharing Rules
			$newbuf .= "\$Contacts_share_read_permission=array('ROLE'=>" . constructTwoDimensionalCharIntSingleValueArray($account_share_read_per['ROLE']) . ",'GROUP'=>" . constructTwoDimensionalValueArray($account_share_read_per['GROUP']) . ");\n";
			$newbuf .= "\$Contacts_share_write_permission=array('ROLE'=>" . constructTwoDimensionalCharIntSingleValueArray($account_share_write_per['ROLE']) . ",'GROUP'=>" . constructTwoDimensionalValueArray($account_share_write_per['GROUP']) . ");\n";
			$sharingPrivileges['permission']['Contacts'] = ['read' => $account_share_read_per, 'write' => $account_share_write_per];

			//Constructing the Account Ticket Related Module Sharing Array
			$acct_related_tkt = getRelatedModuleSharingArray('Accounts', 'HelpDesk', $account_sharingrule_members, $account_share_read_per, $account_share_write_per, $def_org_share);
			$acc_tkt_share_read_per = $acct_related_tkt['read'];
			$acc_tkt_share_write_per = $acct_related_tkt['write'];
			$newbuf .= "\$Accounts_HelpDesk_share_read_permission=array('ROLE'=>" . constructTwoDimensionalCharIntSingleValueArray($acc_tkt_share_read_per['ROLE']) . ",'GROUP'=>" . constructTwoDimensionalValueArray($acc_tkt_share_read_per['GROUP']) . ");\n";
			$newbuf .= "\$Accounts_HelpDesk_share_write_permission=array('ROLE'=>" . constructTwoDimensionalCharIntSingleValueArray($acc_tkt_share_write_per['ROLE']) . ",'GROUP'=>" . constructTwoDimensionalValueArray($acc_tkt_share_write_per['GROUP']) . ");\n";
			$sharingPrivileges['permission']['Accounts_HelpDesk'] = ['read' => $acc_tkt_share_read_per, 'write' => $acc_tkt_share_write_per];

			$custom_modules = getSharingModuleList(['Accounts', 'Contacts']);
			foreach ($custom_modules as &$module_name) {
				$mod_share_perm_array = getUserModuleSharingObjects($module_name, $userid, $def_org_share, $current_user_roles, $parent_roles, $current_user_groups);

				$mod_share_read_perm = $mod_share_perm_array['read'];
				$mod_share_write_perm = $mod_share_perm_array['write'];
				$newbuf .= '$' . $module_name . "_share_read_permission=['ROLE'=>" .
					constructTwoDimensionalCharIntSingleValueArray($mod_share_read_perm['ROLE']) . ",'GROUP'=>" .
					constructTwoDimensionalArray($mod_share_read_perm['GROUP']) . "];\n";
				$newbuf .= '$' . $module_name . "_share_write_permission=['ROLE'=>" .
					constructTwoDimensionalCharIntSingleValueArray($mod_share_write_perm['ROLE']) . ",'GROUP'=>" .
					constructTwoDimensionalArray($mod_share_write_perm['GROUP']) . "];\n";

				$sharingPrivileges['permission'][$module_name] = ['read' => $mod_share_read_perm, 'write' => $mod_share_write_perm];
			}
			$newbuf .= 'return ' . \vtlib\Functions::varExportMin($sharingPrivileges) . ";\n";
			// END
			fputs($handle, $newbuf);
			fclose($handle);

			//Populating Temp Tables
			populateSharingtmptables($userid);
		}
	}
}

/** Gives an array which contains the information for what all roles, groups and user data is to be shared with the spcified user for the specified module 

 * @param $module -- module name:: Type varchar
 * @param $userid -- user id:: Type integer
 * @param $def_org_share -- default organization sharing permission array:: Type array
 * @param $current_user_roles -- roleid:: Type varchar
 * @param $parent_roles -- parent roles:: Type varchar
 * @param $current_user_groups -- user id:: Type integer
 * @returns $mod_share_permission -- array which contains the id of roles,group and users data shared with specifed user for the specified module
 */
function getUserModuleSharingObjects($module, $userid, $def_org_share, $current_user_roles, $parent_roles, $current_user_groups)
{
	$adb = PearDatabase::getInstance();

	$mod_tabid = \includes\Modules::getModuleId($module);

	$mod_share_permission;
	$mod_share_read_permission = [];
	$mod_share_write_permission = [];
	$mod_share_read_permission['ROLE'] = [];
	$mod_share_write_permission['ROLE'] = [];
	$mod_share_read_permission['GROUP'] = [];
	$mod_share_write_permission['GROUP'] = [];

	$share_id_members = [];
	$share_id_groupmembers = [];
	//If Sharing of leads is Private
	if ($def_org_share[$mod_tabid] == 3 || $def_org_share[$mod_tabid] == 0) {
		$role_read_per = [];
		$role_write_per = [];
		$rs_read_per = [];
		$rs_write_per = [];
		$grp_read_per = [];
		$grp_write_per = [];
		//Retreiving from vtiger_role to vtiger_role
		$query = "select vtiger_datashare_role2role.* from vtiger_datashare_role2role inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_role2role.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_role2role.to_roleid=?";
		$result = $adb->pquery($query, array($mod_tabid, $current_user_roles));
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_roleid = $adb->query_result($result, $i, 'share_roleid');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_role_members = [];
			$share_id_roles = [];
			$share_id_roles[] = $share_roleid;
			$share_id_role_members['ROLE'] = $share_id_roles;
			$share_id_members[$shareid] = $share_id_role_members;

			$share_permission = $adb->query_result($result, $i, 'permission');
			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!isset($role_read_per[$share_roleid])) {
						$role_read_per[$share_roleid] = getRoleUserIds($share_roleid);
					}
				}
				if (!isset($role_write_per[$share_roleid])) {
					$role_write_per[$share_roleid] = getRoleUserIds($share_roleid);
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!isset($role_read_per[$share_roleid])) {
					$role_read_per[$share_roleid] = getRoleUserIds($share_roleid);
				}
			}
		}

		//Retreiving from role to rs
		$parRoleList = array();
		foreach ($parent_roles as $par_role_id) {
			array_push($parRoleList, $par_role_id);
		}
		array_push($parRoleList, $current_user_roles);
		$query = "select vtiger_datashare_role2rs.* from vtiger_datashare_role2rs inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_role2rs.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_role2rs.to_roleandsubid in (%s)";
		$query = sprintf($query, generateQuestionMarks($parRoleList));
		$result = $adb->pquery($query, array($mod_tabid, $parRoleList));
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_roleid = $adb->query_result($result, $i, 'share_roleid');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_role_members = [];
			$share_id_roles = [];
			$share_id_roles[] = $share_roleid;
			$share_id_role_members['ROLE'] = $share_id_roles;
			$share_id_members[$shareid] = $share_id_role_members;

			$share_permission = $adb->query_result($result, $i, 'permission');
			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!isset($role_read_per[$share_roleid])) {
						$role_read_per[$share_roleid] = getRoleUserIds($share_roleid);
					}
				}
				if (!isset($role_write_per[$share_roleid])) {
					$role_write_per[$share_roleid] = getRoleUserIds($share_roleid);
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!isset($role_read_per[$share_roleid])) {
					$role_read_per[$share_roleid] = getRoleUserIds($share_roleid);
				}
			}
		}

		//Get roles from Role2Grp
		$groupList = $current_user_groups;
		if (empty($groupList))
			$groupList = array(0);

		if (!empty($groupList)) {
			$query = "select vtiger_datashare_role2group.* from vtiger_datashare_role2group inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_role2group.shareid where vtiger_datashare_module_rel.tabid=?";
			$qparams = array($mod_tabid);

			if (count($groupList) > 0) {
				$query .= " and vtiger_datashare_role2group.to_groupid in (" . generateQuestionMarks($groupList) . ")";
				array_push($qparams, $groupList);
			}
			$result = $adb->pquery($query, $qparams);
			$num_rows = $adb->num_rows($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_roleid = $adb->query_result($result, $i, 'share_roleid');
				$shareid = $adb->query_result($result, $i, 'shareid');
				$share_id_role_members = [];
				$share_id_roles = [];
				$share_id_roles[] = $share_roleid;
				$share_id_role_members['ROLE'] = $share_id_roles;
				$share_id_members[$shareid] = $share_id_role_members;

				$share_permission = $adb->query_result($result, $i, 'permission');
				if ($share_permission == 1) {
					if ($def_org_share[$mod_tabid] == 3) {
						if (!array_key_exists($share_roleid, $role_read_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_read_per[$share_roleid] = $share_role_users;
						}
					}
					if (!array_key_exists($share_roleid, $role_write_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_write_per[$share_roleid] = $share_role_users;
					}
				} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_roleid, $role_read_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_read_per[$share_roleid] = $share_role_users;
					}
				}
			}
		}

		//Get roles from Role2Us
		if (!empty($userid)) {
			$query = 'select vtiger_datashare_role2us.* from vtiger_datashare_role2us inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_role2us.shareid where vtiger_datashare_module_rel.tabid=? && vtiger_datashare_role2us.to_userid = ?';
			$qparams = array($mod_tabid, $userid);

			$result = $adb->pquery($query, $qparams);
			$num_rows = $adb->num_rows($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_roleid = $adb->query_result($result, $i, 'share_roleid');
				$shareid = $adb->query_result($result, $i, 'shareid');
				$share_id_role_members = [];
				$share_id_roles = [];
				$share_id_roles[] = $share_roleid;
				$share_id_role_members['ROLE'] = $share_id_roles;
				$share_id_members[$shareid] = $share_id_role_members;

				$share_permission = $adb->query_result($result, $i, 'permission');
				if ($share_permission == 1) {
					if ($def_org_share[$mod_tabid] == 3) {
						if (!array_key_exists($share_roleid, $role_read_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_read_per[$share_roleid] = $share_role_users;
						}
					}
					if (!array_key_exists($share_roleid, $role_write_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_write_per[$share_roleid] = $share_role_users;
					}
				} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_roleid, $role_read_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_read_per[$share_roleid] = $share_role_users;
					}
				}
			}
		}

		//Retreiving from rs to vtiger_role
		$query = "select vtiger_datashare_rs2role.* from vtiger_datashare_rs2role inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_rs2role.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_rs2role.to_roleid=?";
		$result = $adb->pquery($query, array($mod_tabid, $current_user_roles));
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_rsid = $adb->query_result($result, $i, 'share_roleandsubid');
			$share_roleids = getRoleAndSubordinatesRoleIds($share_rsid);
			$share_permission = $adb->query_result($result, $i, 'permission');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_role_members = [];
			$share_id_roles = [];
			foreach ($share_roleids as $share_roleid) {
				$share_id_roles[] = $share_roleid;


				if ($share_permission == 1) {
					if ($def_org_share[$mod_tabid] == 3) {
						if (!array_key_exists($share_roleid, $role_read_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_read_per[$share_roleid] = $share_role_users;
						}
					}
					if (!array_key_exists($share_roleid, $role_write_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_write_per[$share_roleid] = $share_role_users;
					}
				} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_roleid, $role_read_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_read_per[$share_roleid] = $share_role_users;
					}
				}
			}
			$share_id_role_members['ROLE'] = $share_id_roles;
			$share_id_members[$shareid] = $share_id_role_members;
		}


		//Retreiving from rs to rs
		$parRoleList = array();
		foreach ($parent_roles as $par_role_id) {
			array_push($parRoleList, $par_role_id);
		}
		array_push($parRoleList, $current_user_roles);
		$query = "select vtiger_datashare_rs2rs.* from vtiger_datashare_rs2rs inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_rs2rs.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_rs2rs.to_roleandsubid in (%s)";
		$query = sprintf($query, generateQuestionMarks($parRoleList));
		$result = $adb->pquery($query, array($mod_tabid, $parRoleList));
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_rsid = $adb->query_result($result, $i, 'share_roleandsubid');
			$share_roleids = getRoleAndSubordinatesRoleIds($share_rsid);
			$share_permission = $adb->query_result($result, $i, 'permission');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_role_members = [];
			$share_id_roles = [];
			foreach ($share_roleids as $share_roleid) {

				$share_id_roles[] = $share_roleid;

				if ($share_permission == 1) {
					if ($def_org_share[$mod_tabid] == 3) {
						if (!array_key_exists($share_roleid, $role_read_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_read_per[$share_roleid] = $share_role_users;
						}
					}
					if (!array_key_exists($share_roleid, $role_write_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_write_per[$share_roleid] = $share_role_users;
					}
				} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_roleid, $role_read_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_read_per[$share_roleid] = $share_role_users;
					}
				}
			}
			$share_id_role_members['ROLE'] = $share_id_roles;
			$share_id_members[$shareid] = $share_id_role_members;
		}

		//Get roles from Rs2Grp
		$query = "select vtiger_datashare_rs2grp.* from vtiger_datashare_rs2grp inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_rs2grp.shareid where vtiger_datashare_module_rel.tabid=?";
		$qparams = array($mod_tabid);
		if (count($groupList) > 0) {
			$query .= " and vtiger_datashare_rs2grp.to_groupid in (" . generateQuestionMarks($groupList) . ")";
			array_push($qparams, $groupList);
		}
		$result = $adb->pquery($query, $qparams);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_rsid = $adb->query_result($result, $i, 'share_roleandsubid');
			$share_roleids = getRoleAndSubordinatesRoleIds($share_rsid);
			$share_permission = $adb->query_result($result, $i, 'permission');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_role_members = [];
			$share_id_roles = [];

			foreach ($share_roleids as $share_roleid) {

				$share_id_roles[] = $share_roleid;

				if ($share_permission == 1) {
					if ($def_org_share[$mod_tabid] == 3) {
						if (!array_key_exists($share_roleid, $role_read_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_read_per[$share_roleid] = $share_role_users;
						}
					}
					if (!array_key_exists($share_roleid, $role_write_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_write_per[$share_roleid] = $share_role_users;
					}
				} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_roleid, $role_read_per)) {

						$share_role_users = getRoleUserIds($share_roleid);
						$role_read_per[$share_roleid] = $share_role_users;
					}
				}
			}
			$share_id_role_members['ROLE'] = $share_id_roles;
			$share_id_members[$shareid] = $share_id_role_members;
		}

		//Get roles from Rs2Us
		$query = 'select vtiger_datashare_rs2us.* from vtiger_datashare_rs2us inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_rs2us.shareid where vtiger_datashare_module_rel.tabid=? && to_userid=?';
		$qparams = array($mod_tabid, $userid);

		$result = $adb->pquery($query, $qparams);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_rsid = $adb->query_result($result, $i, 'share_roleandsubid');
			$share_roleids = getRoleAndSubordinatesRoleIds($share_rsid);
			$share_permission = $adb->query_result($result, $i, 'permission');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_role_members = [];
			$share_id_roles = [];

			foreach ($share_roleids as $share_roleid) {
				$share_id_roles[] = $share_roleid;

				if ($share_permission == 1) {
					if ($def_org_share[$mod_tabid] == 3) {
						if (!array_key_exists($share_roleid, $role_read_per)) {
							$share_role_users = getRoleUserIds($share_roleid);
							$role_read_per[$share_roleid] = $share_role_users;
						}
					}
					if (!array_key_exists($share_roleid, $role_write_per)) {
						$share_role_users = getRoleUserIds($share_roleid);
						$role_write_per[$share_roleid] = $share_role_users;
					}
				} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_roleid, $role_read_per)) {
						$share_role_users = getRoleUserIds($share_roleid);
						$role_read_per[$share_roleid] = $share_role_users;
					}
				}
			}
			$share_id_role_members['ROLE'] = $share_id_roles;
			$share_id_members[$shareid] = $share_id_role_members;
		}
		$mod_share_read_permission['ROLE'] = $role_read_per;
		$mod_share_write_permission['ROLE'] = $role_write_per;

		//Retreiving from the grp2role sharing
		$query = "select vtiger_datashare_grp2role.* from vtiger_datashare_grp2role inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_grp2role.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_grp2role.to_roleid=?";
		$result = $adb->pquery($query, array($mod_tabid, $current_user_roles));
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_grpid = $adb->query_result($result, $i, 'share_groupid');
			$share_permission = $adb->query_result($result, $i, 'permission');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_grp_members = [];
			$share_id_grps = [];
			$share_id_grps[] = $share_grpid;


			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_grpid, $grp_read_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($share_grpid);
						$share_grp_users = $focusGrpUsers->group_users;
						$share_grp_subgroups = $focusGrpUsers->group_subgroups;
						$grp_read_per[$share_grpid] = $share_grp_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists($subgrpid, $grp_read_per)) {
								$grp_read_per[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				}
				if (!array_key_exists($share_grpid, $grp_write_per)) {
					$focusGrpUsers = new GetGroupUsers();
					$focusGrpUsers->getAllUsersInGroup($share_grpid);
					$share_grp_users = $focusGrpUsers->group_users;
					$grp_write_per[$share_grpid] = $share_grp_users;
					foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
						if (!array_key_exists($subgrpid, $grp_write_per)) {
							$grp_write_per[$subgrpid] = $subgrpusers;
						}
						if (!in_array($subgrpid, $share_id_grps)) {
							$share_id_grps[] = $subgrpid;
						}
					}
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!array_key_exists($share_grpid, $grp_read_per)) {
					$focusGrpUsers = new GetGroupUsers();
					$focusGrpUsers->getAllUsersInGroup($share_grpid);
					$share_grp_users = $focusGrpUsers->group_users;
					$grp_read_per[$share_grpid] = $share_grp_users;
					foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
						if (!array_key_exists($subgrpid, $grp_read_per)) {
							$grp_read_per[$subgrpid] = $subgrpusers;
						}
						if (!in_array($subgrpid, $share_id_grps)) {
							$share_id_grps[] = $subgrpid;
						}
					}
				}
			}
			$share_id_grp_members['GROUP'] = $share_id_grps;
			$share_id_members[$shareid] = $share_id_grp_members;
		}

		//Retreiving from the grp2rs sharing
		$query = "select vtiger_datashare_grp2rs.* from vtiger_datashare_grp2rs inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_grp2rs.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_grp2rs.to_roleandsubid in (%s)";
		$query = sprintf($query, generateQuestionMarks($parRoleList));
		$result = $adb->pquery($query, array($mod_tabid, $parRoleList));
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_grpid = $adb->query_result($result, $i, 'share_groupid');
			$share_permission = $adb->query_result($result, $i, 'permission');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_grp_members = [];
			$share_id_grps = [];
			$share_id_grps[] = $share_grpid;

			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_grpid, $grp_read_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($share_grpid);
						$share_grp_users = $focusGrpUsers->group_users;
						$grp_read_per[$share_grpid] = $share_grp_users;

						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists($subgrpid, $grp_read_per)) {
								$grp_read_per[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				}
				if (!array_key_exists($share_grpid, $grp_write_per)) {
					$focusGrpUsers = new GetGroupUsers();
					$focusGrpUsers->getAllUsersInGroup($share_grpid);
					$share_grp_users = $focusGrpUsers->group_users;
					$grp_write_per[$share_grpid] = $share_grp_users;
					foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
						if (!array_key_exists($subgrpid, $grp_write_per)) {
							$grp_write_per[$subgrpid] = $subgrpusers;
						}
						if (!in_array($subgrpid, $share_id_grps)) {
							$share_id_grps[] = $subgrpid;
						}
					}
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!array_key_exists($share_grpid, $grp_read_per)) {
					$focusGrpUsers = new GetGroupUsers();
					$focusGrpUsers->getAllUsersInGroup($share_grpid);
					$share_grp_users = $focusGrpUsers->group_users;
					$grp_read_per[$share_grpid] = $share_grp_users;
					foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
						if (!array_key_exists($subgrpid, $grp_read_per)) {
							$grp_read_per[$subgrpid] = $subgrpusers;
						}
						if (!in_array($subgrpid, $share_id_grps)) {
							$share_id_grps[] = $subgrpid;
						}
					}
				}
			}
			$share_id_grp_members['GROUP'] = $share_id_grps;
			$share_id_members[$shareid] = $share_id_grp_members;
		}

		//Retreiving from the grp2us sharing
		$query = "select vtiger_datashare_grp2us.* from vtiger_datashare_grp2us inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_grp2us.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_grp2us.to_userid =?";
		$result = $adb->pquery($query, array($mod_tabid, $userid));
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_grpid = $adb->query_result($result, $i, 'share_groupid');
			$share_permission = $adb->query_result($result, $i, 'permission');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_grp_members = [];
			$share_id_grps = [];
			$share_id_grps[] = $share_grpid;

			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_grpid, $grp_read_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($share_grpid);
						$share_grp_users = $focusGrpUsers->group_users;
						$grp_read_per[$share_grpid] = $share_grp_users;

						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists($subgrpid, $grp_read_per)) {
								$grp_read_per[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				}
				if (!array_key_exists($share_grpid, $grp_write_per)) {
					$focusGrpUsers = new GetGroupUsers();
					$focusGrpUsers->getAllUsersInGroup($share_grpid);
					$share_grp_users = $focusGrpUsers->group_users;
					$grp_write_per[$share_grpid] = $share_grp_users;
					foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
						if (!array_key_exists($subgrpid, $grp_write_per)) {
							$grp_write_per[$subgrpid] = $subgrpusers;
						}
						if (!in_array($subgrpid, $share_id_grps)) {
							$share_id_grps[] = $subgrpid;
						}
					}
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!array_key_exists($share_grpid, $grp_read_per)) {
					$focusGrpUsers = new GetGroupUsers();
					$focusGrpUsers->getAllUsersInGroup($share_grpid);
					$share_grp_users = $focusGrpUsers->group_users;
					$grp_read_per[$share_grpid] = $share_grp_users;
					foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
						if (!array_key_exists($subgrpid, $grp_read_per)) {
							$grp_read_per[$subgrpid] = $subgrpusers;
						}
						if (!in_array($subgrpid, $share_id_grps)) {
							$share_id_grps[] = $subgrpid;
						}
					}
				}
			}
			$share_id_grp_members['GROUP'] = $share_id_grps;
			$share_id_members[$shareid] = $share_id_grp_members;
		}

		//Retreiving from the grp2grp sharing
		$query = "select vtiger_datashare_grp2grp.* from vtiger_datashare_grp2grp inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_grp2grp.shareid where vtiger_datashare_module_rel.tabid=?";
		$qparams = array($mod_tabid);
		if (count($groupList) > 0) {
			$query .= " and vtiger_datashare_grp2grp.to_groupid in (" . generateQuestionMarks($groupList) . ")";
			array_push($qparams, $groupList);
		}
		$result = $adb->pquery($query, $qparams);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_grpid = $adb->query_result($result, $i, 'share_groupid');
			$share_permission = $adb->query_result($result, $i, 'permission');

			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_grp_members = [];
			$share_id_grps = [];
			$share_id_grps[] = $share_grpid;

			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_grpid, $grp_read_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($share_grpid);
						$share_grp_users = $focusGrpUsers->group_users;
						$grp_read_per[$share_grpid] = $share_grp_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists($subgrpid, $grp_read_per)) {
								$grp_read_per[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				}
				if (!array_key_exists($share_grpid, $grp_write_per)) {
					$focusGrpUsers = new GetGroupUsers();
					$focusGrpUsers->getAllUsersInGroup($share_grpid);
					$share_grp_users = $focusGrpUsers->group_users;
					$grp_write_per[$share_grpid] = $share_grp_users;
					foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
						if (!array_key_exists($subgrpid, $grp_write_per)) {
							$grp_write_per[$subgrpid] = $subgrpusers;
						}
						if (!in_array($subgrpid, $share_id_grps)) {
							$share_id_grps[] = $subgrpid;
						}
					}
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!array_key_exists($share_grpid, $grp_read_per)) {
					$focusGrpUsers = new GetGroupUsers();
					$focusGrpUsers->getAllUsersInGroup($share_grpid);
					$share_grp_users = $focusGrpUsers->group_users;
					$grp_read_per[$share_grpid] = $share_grp_users;
					foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
						if (!array_key_exists($subgrpid, $grp_read_per)) {
							$grp_read_per[$subgrpid] = $subgrpusers;
						}
						if (!in_array($subgrpid, $share_id_grps)) {
							$share_id_grps[] = $subgrpid;
						}
					}
				}
			}
			$share_id_grp_members['GROUP'] = $share_id_grps;
			$share_id_members[$shareid] = $share_id_grp_members;
		}

		//Get roles from Us2Us
		$query = 'select vtiger_datashare_us2us.* from vtiger_datashare_us2us inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_us2us.shareid where vtiger_datashare_module_rel.tabid=? && to_userid=?';
		$qparams = array($mod_tabid, $userid);

		$result = $adb->pquery($query, $qparams);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_userid = $adb->query_result($result, $i, 'share_userid');
			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_grp_members = [];
			$share_id_users = [];
			$share_id_users[] = $share_userid;
			$share_id_grp_members['GROUP'] = $share_id_users;
			$share_id_members[$shareid] = $share_id_grp_members;

			$share_permission = $adb->query_result($result, $i, 'permission');
			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_userid, $grp_read_per)) {
						$grp_read_per[$share_userid] = [$share_userid];
					}
				}
				if (!array_key_exists($share_userid, $grp_write_per)) {
					$grp_write_per[$share_userid] = [$share_userid];
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!array_key_exists($share_userid, $grp_read_per)) {
					$grp_read_per[$share_userid] = [$share_userid];
				}
			}
			$share_id_grp_members['GROUP'] = $share_id_users;
			$share_id_members[$shareid] = $share_id_grp_members;
		}

		//Get roles from Us2Grp
		$query = 'select vtiger_datashare_us2grp.* from vtiger_datashare_us2grp inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_us2grp.shareid where vtiger_datashare_module_rel.tabid=?';
		$qparams = array($mod_tabid);
		if (count($groupList) > 0) {
			$query .= " and vtiger_datashare_us2grp.to_groupid in (" . generateQuestionMarks($groupList) . ")";
			array_push($qparams, $groupList);
		}

		$result = $adb->pquery($query, $qparams);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_userid = $adb->query_result($result, $i, 'share_userid');
			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_grp_members = [];
			$share_id_users = [];
			$share_id_users[] = $share_userid;
			$share_id_grp_members['GROUP'] = $share_id_users;
			$share_id_members[$shareid] = $share_id_grp_members;

			$share_permission = $adb->query_result($result, $i, 'permission');
			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_userid, $grp_read_per)) {
						$grp_read_per[$share_userid] = [$share_userid];
					}
				}
				if (!array_key_exists($share_userid, $grp_write_per)) {
					$grp_write_per[$share_userid] = [$share_userid];
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!array_key_exists($share_userid, $grp_read_per)) {
					$grp_read_per[$share_userid] = [$share_userid];
				}
			}
			$share_id_grp_members['GROUP'] = $share_id_users;
			$share_id_members[$shareid] = $share_id_grp_members;
		}

		//Get roles from Us2role
		$query = 'select vtiger_datashare_us2role.* from vtiger_datashare_us2role inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_us2role.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_us2role.to_roleid=?';
		$qparams = array($mod_tabid, $current_user_roles);

		$result = $adb->pquery($query, $qparams);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_userid = $adb->query_result($result, $i, 'share_userid');
			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_grp_members = [];
			$share_id_users = [];
			$share_id_users[] = $share_userid;
			$share_id_grp_members['GROUP'] = $share_id_users;
			$share_id_members[$shareid] = $share_id_grp_members;

			$share_permission = $adb->query_result($result, $i, 'permission');
			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_userid, $grp_read_per)) {
						$grp_read_per[$share_userid] = [$share_userid];
					}
				}
				if (!array_key_exists($share_userid, $grp_write_per)) {
					$grp_write_per[$share_userid] = [$share_userid];
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!array_key_exists($share_userid, $grp_read_per)) {
					$grp_read_per[$share_userid] = [$share_userid];
				}
			}
			$share_id_grp_members['GROUP'] = $share_id_users;
			$share_id_members[$shareid] = $share_id_grp_members;
		}

		//Get roles from Us2rs
		$query = 'select vtiger_datashare_us2rs.* from vtiger_datashare_us2rs inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_us2rs.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_us2rs.to_roleandsubid in (%s)';
		$query = sprintf($query, generateQuestionMarks($parRoleList));
		$qparams = array($mod_tabid, $parRoleList);

		$result = $adb->pquery($query, $qparams);
		$num_rows = $adb->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$share_userid = $adb->query_result($result, $i, 'share_userid');
			$shareid = $adb->query_result($result, $i, 'shareid');
			$share_id_grp_members = [];
			$share_id_users = [];
			$share_id_users[] = $share_userid;
			$share_id_grp_members['GROUP'] = $share_id_users;
			$share_id_members[$shareid] = $share_id_grp_members;

			$share_permission = $adb->query_result($result, $i, 'permission');
			if ($share_permission == 1) {
				if ($def_org_share[$mod_tabid] == 3) {
					if (!array_key_exists($share_userid, $grp_read_per)) {
						$grp_read_per[$share_userid] = [$share_userid];
					}
				}
				if (!array_key_exists($share_userid, $grp_write_per)) {
					$grp_write_per[$share_userid] = [$share_userid];
				}
			} elseif ($share_permission == 0 && $def_org_share[$mod_tabid] == 3) {
				if (!array_key_exists($share_userid, $grp_read_per)) {
					$grp_read_per[$share_userid] = [$share_userid];
				}
			}
			$share_id_grp_members['GROUP'] = $share_id_users;
			$share_id_members[$shareid] = $share_id_grp_members;
		}
		$mod_share_read_permission['GROUP'] = $grp_read_per;
		$mod_share_write_permission['GROUP'] = $grp_write_per;
	}
	$mod_share_permission['read'] = $mod_share_read_permission;
	$mod_share_permission['write'] = $mod_share_write_permission;
	$mod_share_permission['sharingrules'] = $share_id_members;
	return $mod_share_permission;
}

/** Gives an array which contains the information for what all roles, groups and user's related module data that is to be shared  for the specified parent module and shared module 

 * @param $par_mod -- parent module name:: Type varchar
 * @param $share_mod -- shared module name:: Type varchar
 * @param $userid -- user id:: Type integer
 * @param $def_org_share -- default organization sharing permission array:: Type array
 * @param $mod_sharingrule_members -- Sharing Rule Members array:: Type array
 * @param $$mod_share_read_per -- Sharing Module Read Permission array:: Type array
 * @param $$mod_share_write_per -- Sharing Module Write Permission array:: Type array
 * @returns $related_mod_sharing_permission; -- array which contains the id of roles,group and users related module data to be shared 
 */
function getRelatedModuleSharingArray($par_mod, $share_mod, $mod_sharingrule_members, $mod_share_read_per, $mod_share_write_per, $def_org_share)
{

	$adb = PearDatabase::getInstance();
	$related_mod_sharing_permission = [];
	$mod_share_read_permission = [];
	$mod_share_write_permission = [];

	$mod_share_read_permission['ROLE'] = [];
	$mod_share_write_permission['ROLE'] = [];
	$mod_share_read_permission['GROUP'] = [];
	$mod_share_write_permission['GROUP'] = [];

	$par_mod_id = \includes\Modules::getModuleId($par_mod);
	$share_mod_id = \includes\Modules::getModuleId($share_mod);

	if ($def_org_share[$share_mod_id] == 3 || $def_org_share[$share_mod_id] == 0) {
		$role_read_per = [];
		$role_write_per = [];
		$grp_read_per = [];
		$grp_write_per = [];

		foreach ($mod_sharingrule_members as $sharingid => $sharingInfoArr) {
			$query = "select vtiger_datashare_relatedmodule_permission.* from vtiger_datashare_relatedmodule_permission inner join vtiger_datashare_relatedmodules on vtiger_datashare_relatedmodules.datashare_relatedmodule_id=vtiger_datashare_relatedmodule_permission.datashare_relatedmodule_id where vtiger_datashare_relatedmodule_permission.shareid=? and vtiger_datashare_relatedmodules.tabid=? and vtiger_datashare_relatedmodules.relatedto_tabid=?";
			$result = $adb->pquery($query, array($sharingid, $par_mod_id, $share_mod_id));
			$share_permission = $adb->query_result($result, 0, 'permission');

			foreach ($sharingInfoArr as $shareType => $shareEntArr) {
				foreach ($shareEntArr as $key => $shareEntId) {
					if ($shareType == 'ROLE') {
						if ($share_permission == 1) {
							if ($def_org_share[$share_mod_id] == 3) {
								if (!array_key_exists($shareEntId, $role_read_per)) {
									if (array_key_exists($shareEntId, $mod_share_read_per['ROLE'])) {
										$share_role_users = $mod_share_read_per['ROLE'][$shareEntId];
									} elseif (array_key_exists($shareEntId, $mod_share_write_per['ROLE'])) {
										$share_role_users = $mod_share_write_per['ROLE'][$shareEntId];
									} else {

										$share_role_users = getRoleUserIds($shareEntId);
									}

									$role_read_per[$shareEntId] = $share_role_users;
								}
							}
							if (!array_key_exists($shareEntId, $role_write_per)) {
								if (array_key_exists($shareEntId, $mod_share_read_per['ROLE'])) {
									$share_role_users = $mod_share_read_per['ROLE'][$shareEntId];
								} elseif (array_key_exists($shareEntId, $mod_share_write_per['ROLE'])) {
									$share_role_users = $mod_share_write_per['ROLE'][$shareEntId];
								} else {

									$share_role_users = getRoleUserIds($shareEntId);
								}

								$role_write_per[$shareEntId] = $share_role_users;
							}
						} elseif ($share_permission == 0 && $def_org_share[$share_mod_id] == 3) {
							if (!array_key_exists($shareEntId, $role_read_per)) {
								if (array_key_exists($shareEntId, $mod_share_read_per['ROLE'])) {
									$share_role_users = $mod_share_read_per['ROLE'][$shareEntId];
								} elseif (array_key_exists($shareEntId, $mod_share_write_per['ROLE'])) {
									$share_role_users = $mod_share_write_per['ROLE'][$shareEntId];
								} else {

									$share_role_users = getRoleUserIds($shareEntId);
								}

								$role_read_per[$shareEntId] = $share_role_users;
							}
						}
					} elseif ($shareType == 'GROUP') {
						if ($share_permission == 1) {
							if ($def_org_share[$share_mod_id] == 3) {

								if (!array_key_exists($shareEntId, $grp_read_per)) {
									if (array_key_exists($shareEntId, $mod_share_read_per['GROUP'])) {
										$share_grp_users = $mod_share_read_per['GROUP'][$shareEntId];
									} elseif (array_key_exists($shareEntId, $mod_share_write_per['GROUP'])) {
										$share_grp_users = $mod_share_write_per['GROUP'][$shareEntId];
									} else {
										$focusGrpUsers = new GetGroupUsers();
										$focusGrpUsers->getAllUsersInGroup($shareEntId);
										$share_grp_users = $focusGrpUsers->group_users;

										foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
											if (!array_key_exists($subgrpid, $grp_read_per)) {
												$grp_read_per[$subgrpid] = $subgrpusers;
											}
										}
									}

									$grp_read_per[$shareEntId] = $share_grp_users;
								}
							}
							if (!array_key_exists($shareEntId, $grp_write_per)) {
								if (!array_key_exists($shareEntId, $grp_write_per)) {
									if (array_key_exists($shareEntId, $mod_share_read_per['GROUP'])) {
										$share_grp_users = $mod_share_read_per['GROUP'][$shareEntId];
									} elseif (array_key_exists($shareEntId, $mod_share_write_per['GROUP'])) {
										$share_grp_users = $mod_share_write_per['GROUP'][$shareEntId];
									} else {
										$focusGrpUsers = new GetGroupUsers();
										$focusGrpUsers->getAllUsersInGroup($shareEntId);
										$share_grp_users = $focusGrpUsers->group_users;
										foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
											if (!array_key_exists($subgrpid, $grp_write_per)) {
												$grp_write_per[$subgrpid] = $subgrpusers;
											}
										}
									}

									$grp_write_per[$shareEntId] = $share_grp_users;
								}
							}
						} elseif ($share_permission == 0 && $def_org_share[$share_mod_id] == 3) {
							if (!array_key_exists($shareEntId, $grp_read_per)) {
								if (array_key_exists($shareEntId, $mod_share_read_per['GROUP'])) {
									$share_grp_users = $mod_share_read_per['GROUP'][$shareEntId];
								} elseif (array_key_exists($shareEntId, $mod_share_write_per['GROUP'])) {
									$share_grp_users = $mod_share_write_per['GROUP'][$shareEntId];
								} else {
									$focusGrpUsers = new GetGroupUsers();
									$focusGrpUsers->getAllUsersInGroup($shareEntId);
									$share_grp_users = $focusGrpUsers->group_users;
									foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
										if (!array_key_exists($subgrpid, $grp_read_per)) {
											$grp_read_per[$subgrpid] = $subgrpusers;
										}
									}
								}

								$grp_read_per[$shareEntId] = $share_grp_users;
							}
						}
					}
				}
			}
		}
		$mod_share_read_permission['ROLE'] = $role_read_per;
		$mod_share_write_permission['ROLE'] = $role_write_per;
		$mod_share_read_permission['GROUP'] = $grp_read_per;
		$mod_share_write_permission['GROUP'] = $grp_write_per;
	}
	$related_mod_sharing_permission['read'] = $mod_share_read_permission;
	$related_mod_sharing_permission['write'] = $mod_share_write_permission;
	return $related_mod_sharing_permission;
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructArray($var)
{
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $key => $value) {
			$code .= "'" . $key . "'=>" . $value . ',';
		}
		$code .= ']';
		return $code;
	}
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructSingleStringValueArray($var)
{

	$size = sizeof($var);
	$i = 1;
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $key => $value) {
			if ($i < $size) {
				$code .= $key . "=>'" . $value . "',";
			} else {
				$code .= $key . "=>'" . $value . "'";
			}
			$i++;
		}
		$code .= ']';
		return $code;
	}
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructSingleStringKeyAndValueArray($var)
{

	$size = sizeof($var);
	$i = 1;
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $key => $value) {
			if ($i < $size) {
				$code .= "'" . $key . "'=>" . $value . ",";
			} else {
				$code .= "'" . $key . "'=>" . $value;
			}
			$i++;
		}
		$code .= ']';
		return $code;
	}
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructSingleStringKeyValueArray($var)
{
	$adb = PearDatabase::getInstance();
	$size = sizeof($var);
	$i = 1;
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $key => $value) {
			//fix for signatue quote(') issue
			$value = $adb->sql_escape_string($value);
			if ($i < $size) {
				$code .= "'" . $key . "'=>" . $value . ",";
			} else {
				$code .= "'" . $key . "'=>" . $value;
			}
			$i++;
		}
		$code .= ']';
		return $code;
	}
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructSingleArray($var)
{
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $value) {
			$code .= $value . ',';
		}
		$code .= ']';
		return $code;
	}
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructSingleCharArray($var)
{
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $value) {
			$code .="'" . $value . "',";
		}
		$code .= ']';
		return $code;
	}
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructTwoDimensionalArray($var)
{
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $key => $secarr) {
			$code .= $key . '=>[';
			foreach ($secarr as $seckey => $secvalue) {
				$code .= $seckey . '=>' . $secvalue . ',';
			}
			$code .= '],';
		}
		$code .= ']';
		return $code;
	}
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructTwoDimensionalValueArray($var)
{
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $key => $secarr) {
			$code .= $key . '=>array(';
			foreach ($secarr as $seckey => $secvalue) {
				$code .= $secvalue . ',';
			}
			$code .= '),';
		}
		$code .= ']';
		return $code;
	}
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructTwoDimensionalCharIntSingleArray($var)
{
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $key => $secarr) {
			$code .= "'" . $key . "'=>[";
			foreach ($secarr as $seckey => $secvalue) {
				$code .= $seckey . ',';
			}
			$code .= '],';
		}
		$code .= ']';
		return $code;
	}
}

/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file 

 * @param $var -- input array:: Type array
 * @returns $code -- contains the whole array in a single string:: Type array 
 */
function constructTwoDimensionalCharIntSingleValueArray($var)
{
	if (is_array($var)) {
		$code = '[';
		foreach ($var as $key => $secarr) {
			$code .= "'" . $key . "'=>[";
			foreach ($secarr as $seckey => $secvalue) {
				$code .= $secvalue . ',';
			}
			$code .= '],';
		}
		$code .= ']';
		return $code;
	}
}

/** Function to populate the read/wirte Sharing permissions data of user/groups for the specified user into the database 
 * @param $userid -- user id:: Type integer
 */
function populateSharingtmptables($userid)
{
	$adb = PearDatabase::getInstance();
	\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/sharing_privileges_' . $userid . '.php');
	require('user_privileges/sharing_privileges_' . $userid . '.php');
	//Deleting from the existing vtiger_tables
	$table_arr = Array('vtiger_tmp_read_user_sharing_per', 'vtiger_tmp_write_user_sharing_per', 'vtiger_tmp_read_group_sharing_per', 'vtiger_tmp_write_group_sharing_per', 'vtiger_tmp_read_user_rel_sharing_per', 'vtiger_tmp_write_user_rel_sharing_per', 'vtiger_tmp_read_group_rel_sharing_per', 'vtiger_tmp_write_group_rel_sharing_per');
	foreach ($table_arr as $tabname) {
		$adb->delete($tabname, 'userid = ?', [$userid]);
	}

	// Look up for modules for which sharing access is enabled.
	$modules = \vtlib\Functions::getAllModules(true, true, 0, false, 0);
	$sharingArray = array_column($modules, 'name');
	foreach ($sharingArray as $module) {
		$module_sharing_read_permvar = $module . '_share_read_permission';
		$module_sharing_write_permvar = $module . '_share_write_permission';

		populateSharingPrivileges('USER', $userid, $module, 'read', $$module_sharing_read_permvar);
		populateSharingPrivileges('USER', $userid, $module, 'write', $$module_sharing_write_permvar);
		populateSharingPrivileges('GROUP', $userid, $module, 'read', $$module_sharing_read_permvar);
		populateSharingPrivileges('GROUP', $userid, $module, 'write', $$module_sharing_write_permvar);
	}
	//Populating Values into the temp related sharing tables
	foreach ($related_module_share as $rel_tab_id => $tabid_arr) {
		$rel_tab_name = getTabname($rel_tab_id);
		if (!empty($rel_tab_name)) {
			foreach ($tabid_arr as $taid) {
				$tab_name = getTabname($taid);

				$relmodule_sharing_read_permvar = $tab_name . '_' . $rel_tab_name . '_share_read_permission';
				$relmodule_sharing_write_permvar = $tab_name . '_' . $rel_tab_name . '_share_write_permission';

				populateRelatedSharingPrivileges('USER', $userid, $tab_name, $rel_tab_name, 'read', $$relmodule_sharing_read_permvar);
				populateRelatedSharingPrivileges('USER', $userid, $tab_name, $rel_tab_name, 'write', $$relmodule_sharing_write_permvar);
				populateRelatedSharingPrivileges('GROUP', $userid, $tab_name, $rel_tab_name, 'read', $$relmodule_sharing_read_permvar);
				populateRelatedSharingPrivileges('GROUP', $userid, $tab_name, $rel_tab_name, 'write', $$relmodule_sharing_write_permvar);
			}
		}
	}
}

/** Function to populate the read/wirte Sharing permissions data for the specified user into the database 
 * @param $userid -- user id:: Type integer
 * @param $enttype -- can have the value of User or Group:: Type varchar
 * @param $module -- module name:: Type varchar
 * @param $pertype -- can have the value of read or write:: Type varchar
 * @param $var_name_arr - Variable to use instead of including the sharing access again
 */
function populateSharingPrivileges($enttype, $userid, $module, $pertype, $var_name_arr = false)
{
	$adb = PearDatabase::getInstance();
	$tabid = \includes\Modules::getModuleId($module);

	if (!$var_name_arr) {
		\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/sharing_privileges_' . $userid . '.php');
		require('user_privileges/sharing_privileges_' . $userid . '.php');
	}

	if ($enttype == 'USER') {
		if ($pertype == 'read') {
			$table_name = 'vtiger_tmp_read_user_sharing_per';
			$var_name = $module . '_share_read_permission';
		} elseif ($pertype == 'write') {
			$table_name = 'vtiger_tmp_write_user_sharing_per';
			$var_name = $module . '_share_write_permission';
		}
		// Lookup for the variable if not set through function argument		
		if (!$var_name_arr)
			$var_name_arr = $$var_name;
		$user_arr = [];
		if (sizeof($var_name_arr['ROLE']) > 0) {
			foreach ($var_name_arr['ROLE'] as $roleid => $roleusers) {

				foreach ($roleusers as $user_id) {
					if (!in_array($user_id, $user_arr)) {
						$query = "insert into " . $table_name . " values(?,?,?)";
						$adb->pquery($query, array($userid, $tabid, $user_id));
						$user_arr[] = $user_id;
					}
				}
			}
		}
		if (sizeof($var_name_arr['GROUP']) > 0) {
			foreach ($var_name_arr['GROUP'] as $grpid => $grpusers) {
				foreach ($grpusers as $user_id) {
					if (!in_array($user_id, $user_arr)) {
						$query = "insert into " . $table_name . " values(?,?,?)";
						$adb->pquery($query, array($userid, $tabid, $user_id));
						$user_arr[] = $user_id;
					}
				}
			}
		}
	} elseif ($enttype == 'GROUP') {
		if ($pertype == 'read') {
			$table_name = 'vtiger_tmp_read_group_sharing_per';
			$var_name = $module . '_share_read_permission';
		} elseif ($pertype == 'write') {
			$table_name = 'vtiger_tmp_write_group_sharing_per';
			$var_name = $module . '_share_write_permission';
		}
		// Lookup for the variable if not set through function argument
		if (!$var_name_arr)
			$var_name_arr = $$var_name;
		$grp_arr = [];
		if (sizeof($var_name_arr['GROUP']) > 0) {

			foreach ($var_name_arr['GROUP'] as $grpid => $grpusers) {
				if (!in_array($grpid, $grp_arr)) {
					$query = "insert into " . $table_name . " values(?,?,?)";
					$adb->pquery($query, array($userid, $tabid, $grpid));
					$grp_arr[] = $grpid;
				}
			}
		}
	}
}

/** Function to populate the read/wirte Sharing permissions related module data for the specified user into the database 
 * @param $userid -- user id:: Type integer
 * @param $enttype -- can have the value of User or Group:: Type varchar
 * @param $module -- module name:: Type varchar
 * @param $relmodule -- related module name:: Type varchar
 * @param $pertype -- can have the value of read or write:: Type varchar
 * @param $var_name_arr - Variable to use instead of including the sharing access again
 */
function populateRelatedSharingPrivileges($enttype, $userid, $module, $relmodule, $pertype, $var_name_arr = false)
{
	$adb = PearDatabase::getInstance();
	$tabid = \includes\Modules::getModuleId($module);
	$reltabid = \includes\Modules::getModuleId($relmodule);

	if (!$var_name_arr) {
		\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/sharing_privileges_' . $userid . '.php');
		require('user_privileges/sharing_privileges_' . $userid . '.php');
	}

	if ($enttype == 'USER') {
		if ($pertype == 'read') {
			$table_name = 'vtiger_tmp_read_user_rel_sharing_per';
			$var_name = $module . '_' . $relmodule . '_share_read_permission';
		} elseif ($pertype == 'write') {
			$table_name = 'vtiger_tmp_write_user_rel_sharing_per';
			$var_name = $module . '_' . $relmodule . '_share_write_permission';
		}
		// Lookup for the variable if not set through function argument
		if (!$var_name_arr)
			$var_name_arr = $$var_name;
		$user_arr = [];
		if (sizeof($var_name_arr['ROLE']) > 0) {
			foreach ($var_name_arr['ROLE'] as $roleid => $roleusers) {

				foreach ($roleusers as $user_id) {
					if (!in_array($user_id, $user_arr)) {
						$query = "insert into " . $table_name . " values(?,?,?,?)";
						$adb->pquery($query, array($userid, $tabid, $reltabid, $user_id));
						$user_arr[] = $user_id;
					}
				}
			}
		}
		if (sizeof($var_name_arr['GROUP']) > 0) {
			foreach ($var_name_arr['GROUP'] as $grpid => $grpusers) {
				foreach ($grpusers as $user_id) {
					if (!in_array($user_id, $user_arr)) {
						$query = "insert into " . $table_name . " values(?,?,?,?)";
						$adb->pquery($query, array($userid, $tabid, $reltabid, $user_id));
						$user_arr[] = $user_id;
					}
				}
			}
		}
	} elseif ($enttype == 'GROUP') {
		if ($pertype == 'read') {
			$table_name = 'vtiger_tmp_read_group_rel_sharing_per';
			$var_name = $module . '_' . $relmodule . '_share_read_permission';
		} elseif ($pertype == 'write') {
			$table_name = 'vtiger_tmp_write_group_rel_sharing_per';
			$var_name = $module . '_' . $relmodule . '_share_write_permission';
		}
		// Lookup for the variable if not set through function argument
		if (!$var_name_arr)
			$var_name_arr = $$var_name;
		$grp_arr = [];
		if (sizeof($var_name_arr['GROUP']) > 0) {

			foreach ($var_name_arr['GROUP'] as $grpid => $grpusers) {
				if (!in_array($grpid, $grp_arr)) {
					$query = "insert into " . $table_name . " values(?,?,?,?)";
					$adb->pquery($query, array($userid, $tabid, $reltabid, $grpid));
					$grp_arr[] = $grpid;
				}
			}
		}
	}
}

?>
