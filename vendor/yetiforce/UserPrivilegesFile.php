<?php
namespace App;

/**
 * Create user privileges file class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class UserPrivilegesFile
{

	/** Creates a file with all the user, user-role,user-profile, user-groups informations
	 * @param int $userId
	 * @returns User_Privileges_Userid file under the User_Privileges Directory
	 */
	public static function createUserPrivilegesfile($userid)
	{
		$handle = @fopen(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges/user_privileges_' . $userid . '.php', "w+");

		if ($handle) {
			$newbuf = '';
			$newbuf .= "<?php\n";
			$user_focus = \CRMEntity::getInstance('Users');
			$user_focus->retrieveEntityInfo($userid, 'Users');
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
				$newbuf .= "\$user_info=" . Utils::varExport($userInfo) . ";\n";
			} else {
				$newbuf .= "\$is_admin=false;\n";

				$globalPermissionArr = PrivilegeUtil::getCombinedUserGlobalPermissions($userid);
				$tabsPermissionArr = PrivilegeUtil::getCombinedUserTabsPermissions($userid);
				$actionPermissionArr = getCombinedUserActionPermissions($userid);
				$user_role = PrivilegeUtil::getRoleByUsers($userid);
				$user_role_info = PrivilegeUtil::getRoleDetail($user_role);
				$user_role_parent = $user_role_info['parentrole'];
				$subRoles = PrivilegeUtil::getRoleSubordinates($user_role);
				$subRoleAndUsers = getSubordinateRoleAndUsers($user_role);
				$parentRoles = PrivilegeUtil::getParentRole($user_role);
				$newbuf .= "\$current_user_roles='" . $user_role . "';\n";
				$newbuf .= "\$current_user_parent_role_seq='" . $user_role_parent . "';\n";
				$newbuf .= "\$current_user_profiles=" . Utils::varExport(PrivilegeUtil::getProfilesByRole($user_role)) . ";\n";
				$newbuf .= "\$profileGlobalPermission=" . Utils::varExport($globalPermissionArr) . ";\n";
				$newbuf .= "\$profileTabsPermission=" . Utils::varExport($tabsPermissionArr) . ";\n";
				$newbuf .= "\$profileActionPermission=" . Utils::varExport($actionPermissionArr) . ";\n";
				$newbuf .= "\$current_user_groups=" . Utils::varExport(PrivilegeUtil::getAllGroupsByUser($userid)) . ";\n";
				$newbuf .= "\$subordinate_roles=" . Utils::varExport($subRoles) . ";\n";
				$newbuf .= "\$parent_roles=" . Utils::varExport($parentRoles) . ";\n";
				$newbuf .= "\$subordinate_roles_users=" . Utils::varExport($subRoleAndUsers) . ";\n";
				$newbuf .= "\$user_info=" . Utils::varExport($userInfo) . ";\n";
			}
			fputs($handle, $newbuf);
			fclose($handle);
			PrivilegeFile::createUserPrivilegesFile($userid);
			\Users_Privileges_Model::clearCache($userid);
			User::clearCache($userid);
		}
	}

	/**
	 * Creates a file with all the organization default sharing permissions 
	 * and custom sharing permissins specific for the specified user. 
	 * In this file the information of the other users whose data is shared with the specified user is stored.
	 * @param int $userId
	 * @returns sharing_privileges_userid file under the user_privileges directory
	 */
	public static function createUserSharingPrivilegesfile($userid)
	{
		\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/user_privileges_' . $userid . '.php');
		require('user_privileges/user_privileges_' . $userid . '.php');
		$handle = @fopen(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges/sharing_privileges_' . $userid . '.php', "w+");

		if ($handle) {
			$newbuf = "<?php\n";
			$user_focus = \CRMEntity::getInstance('Users');
			$user_focus->retrieveEntityInfo($userid, 'Users');
			if ($user_focus->is_admin == 'on') {
				fputs($handle, $newbuf);
				fclose($handle);
				return;
			} else {
				$sharingPrivileges = [];
				//Constructig the Default Org Share Array
				$def_org_share = PrivilegeUtil::getAllDefaultSharingAction();
				$newbuf .= "\$defaultOrgSharingPermission=" . Utils::varExport($def_org_share) . ";\n";
				$sharingPrivileges['defOrgShare'] = $def_org_share;

				$relatedModuleShare = PrivilegeUtil::getDatashareRelatedModules();
				$newbuf .= "\$related_module_share=" . Utils::varExport($relatedModuleShare) . ";\n";
				$sharingPrivileges['relatedModuleShare'] = $relatedModuleShare;
				//Constructing Account Sharing Rules
				$account_share_per_array = PrivilegeUtil::getUserModuleSharingObjects('Accounts', $userid, $def_org_share, $current_user_roles, $parent_roles, $current_user_groups);
				$account_share_read_per = $account_share_per_array['read'];
				$account_share_write_per = $account_share_per_array['write'];
				$account_sharingrule_members = $account_share_per_array['sharingrules'];
				$newbuf .= "\$Accounts_share_read_permission=array('ROLE'=>" . Utils::varExport($account_share_read_per['ROLE']) . ",'GROUP'=>" . Utils::varExport($account_share_read_per['GROUP']) . ");\n";
				$newbuf .= "\$Accounts_share_write_permission=array('ROLE'=>" . Utils::varExport($account_share_write_per['ROLE']) . ",'GROUP'=>" . Utils::varExport($account_share_write_per['GROUP']) . ");\n";
				$sharingPrivileges['permission']['Accounts'] = ['read' => $account_share_read_per, 'write' => $account_share_write_per];
				//Constructing Contact Sharing Rules
				$newbuf .= "\$Contacts_share_read_permission=array('ROLE'=>" . Utils::varExport($account_share_read_per['ROLE']) . ",'GROUP'=>" . Utils::varExport($account_share_read_per['GROUP']) . ");\n";
				$newbuf .= "\$Contacts_share_write_permission=array('ROLE'=>" . Utils::varExport($account_share_write_per['ROLE']) . ",'GROUP'=>" . Utils::varExport($account_share_write_per['GROUP']) . ");\n";
				$sharingPrivileges['permission']['Contacts'] = ['read' => $account_share_read_per, 'write' => $account_share_write_per];

				//Constructing the Account Ticket Related Module Sharing Array
				$acct_related_tkt = static::getRelatedModuleSharingArray('Accounts', 'HelpDesk', $account_sharingrule_members, $account_share_read_per, $account_share_write_per, $def_org_share);
				$acc_tkt_share_read_per = $acct_related_tkt['read'];
				$acc_tkt_share_write_per = $acct_related_tkt['write'];
				$newbuf .= "\$Accounts_HelpDesk_share_read_permission=array('ROLE'=>" . Utils::varExport($acc_tkt_share_read_per['ROLE']) . ",'GROUP'=>" . Utils::varExport($acc_tkt_share_read_per['GROUP']) . ");\n";
				$newbuf .= "\$Accounts_HelpDesk_share_write_permission=array('ROLE'=>" . Utils::varExport($acc_tkt_share_write_per['ROLE']) . ",'GROUP'=>" . Utils::varExport($acc_tkt_share_write_per['GROUP']) . ");\n";
				$sharingPrivileges['permission']['Accounts_HelpDesk'] = ['read' => $acc_tkt_share_read_per, 'write' => $acc_tkt_share_write_per];

				$custom_modules = Module::getSharingModuleList(['Accounts', 'Contacts']);
				foreach ($custom_modules as &$module_name) {
					$mod_share_perm_array = PrivilegeUtil::getUserModuleSharingObjects($module_name, $userid, $def_org_share, $current_user_roles, $parent_roles, $current_user_groups);

					$mod_share_read_perm = $mod_share_perm_array['read'];
					$mod_share_write_perm = $mod_share_perm_array['write'];
					$newbuf .= '$' . $module_name . "_share_read_permission=['ROLE'=>" .
						Utils::varExport($mod_share_read_perm['ROLE']) . ",'GROUP'=>" .
						Utils::varExport($mod_share_read_perm['GROUP']) . "];\n";
					$newbuf .= '$' . $module_name . "_share_write_permission=['ROLE'=>" .
						Utils::varExport($mod_share_write_perm['ROLE']) . ",'GROUP'=>" .
						Utils::varExport($mod_share_write_perm['GROUP']) . "];\n";

					$sharingPrivileges['permission'][$module_name] = ['read' => $mod_share_read_perm, 'write' => $mod_share_write_perm];
				}
				$newbuf .= 'return ' . Utils::varExport($sharingPrivileges) . ";\n";
				// END
				fputs($handle, $newbuf);
				fclose($handle);

				//Populating Temp Tables
				static::populateSharingtmptables($userid);
			}
		}
	}

	/**
	 * Gives an array which contains the information for what all roles,
	 * groups and user's related module data that is to be shared  for the specified parent module and shared module
	 * @param string $parMod
	 * @param string $shareMod
	 * @param array $modSharingRuleMembers
	 * @param array $modShareReadPer
	 * @param array $modShareWritePer
	 * @param array $defOrgShare
	 * @return array
	 */
	public static function getRelatedModuleSharingArray($par_mod, $share_mod, $mod_sharingrule_members, $mod_share_read_per, $mod_share_write_per, $def_org_share)
	{

		$adb = \PearDatabase::getInstance();
		$related_mod_sharing_permission = [];
		$mod_share_read_permission = [];
		$mod_share_write_permission = [];

		$mod_share_read_permission['ROLE'] = [];
		$mod_share_write_permission['ROLE'] = [];
		$mod_share_read_permission['GROUP'] = [];
		$mod_share_write_permission['GROUP'] = [];

		$par_mod_id = Module::getModuleId($par_mod);
		$share_mod_id = Module::getModuleId($share_mod);

		if ($def_org_share[$share_mod_id] == 3 || $def_org_share[$share_mod_id] == 0) {
			$role_read_per = [];
			$role_write_per = [];
			$grp_read_per = [];
			$grp_write_per = [];

			foreach ($mod_sharingrule_members as $sharingid => $sharingInfoArr) {
				$query = "select vtiger_datashare_relatedmodule_permission.* from vtiger_datashare_relatedmodule_permission inner join vtiger_datashare_relatedmodules on vtiger_datashare_relatedmodules.datashare_relatedmodule_id=vtiger_datashare_relatedmodule_permission.datashare_relatedmodule_id where vtiger_datashare_relatedmodule_permission.shareid=? and vtiger_datashare_relatedmodules.tabid=? and vtiger_datashare_relatedmodules.relatedto_tabid=?";
				$result = $adb->pquery($query, array($sharingid, $par_mod_id, $share_mod_id));
				$share_permission = $adb->queryResult($result, 0, 'permission');

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

											$share_role_users = PrivilegeUtil::getUsersByRole($shareEntId);
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

										$share_role_users = PrivilegeUtil::getUsersByRole($shareEntId);
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

										$share_role_users = PrivilegeUtil::getUsersByRole($shareEntId);
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
											$usersByGroup = PrivilegeUtil::getUsersByGroup($shareEntId, true);
											$share_grp_users = $usersByGroup['users'];
											foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
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
											$usersByGroup = PrivilegeUtil::getUsersByGroup($shareEntId, true);
											$share_grp_users = $usersByGroup['users'];
											foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
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
										$usersByGroup = PrivilegeUtil::getUsersByGroup($shareEntId, true);
										$share_grp_users = $usersByGroup['users'];
										foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
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

	/** Function to populate the read/wirte Sharing permissions data of user/groups for the specified user into the database
	 * @param int $userId
	 */
	public static function populateSharingtmptables($userid)
	{
		$adb = \PearDatabase::getInstance();
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

			static::populateSharingPrivileges('USER', $userid, $module, 'read', $$module_sharing_read_permvar);
			static::populateSharingPrivileges('USER', $userid, $module, 'write', $$module_sharing_write_permvar);
			static::populateSharingPrivileges('GROUP', $userid, $module, 'read', $$module_sharing_read_permvar);
			static::populateSharingPrivileges('GROUP', $userid, $module, 'write', $$module_sharing_write_permvar);
		}
		//Populating Values into the temp related sharing tables
		foreach ($related_module_share as $rel_tab_id => $tabid_arr) {
			$rel_tab_name = Module::getModuleName($rel_tab_id);
			if (!empty($rel_tab_name)) {
				foreach ($tabid_arr as $taid) {
					$tab_name = Module::getModuleName($taid);

					$relmodule_sharing_read_permvar = $tab_name . '_' . $rel_tab_name . '_share_read_permission';
					$relmodule_sharing_write_permvar = $tab_name . '_' . $rel_tab_name . '_share_write_permission';

					static::populateRelatedSharingPrivileges('USER', $userid, $tab_name, $rel_tab_name, 'read', $$relmodule_sharing_read_permvar);
					static::populateRelatedSharingPrivileges('USER', $userid, $tab_name, $rel_tab_name, 'write', $$relmodule_sharing_write_permvar);
					static::populateRelatedSharingPrivileges('GROUP', $userid, $tab_name, $rel_tab_name, 'read', $$relmodule_sharing_read_permvar);
					static::populateRelatedSharingPrivileges('GROUP', $userid, $tab_name, $rel_tab_name, 'write', $$relmodule_sharing_write_permvar);
				}
			}
		}
	}

	/**
	 * Function to populate the read/wirte Sharing permissions data for the specified user into the database
	 * @param string $entType
	 * @param int $userId
	 * @param string $module
	 * @param string $perType
	 * @param boolean $varNameArr
	 */
	public static function populateSharingPrivileges($enttype, $userid, $module, $pertype, $var_name_arr = false)
	{
		$adb = \PearDatabase::getInstance();
		$tabid = Module::getModuleId($module);

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

	/**
	 * Function to populate the read/wirte Sharing permissions related module data for the specified user into the database
	 * @param string $entType
	 * @param int $userId
	 * @param string $module
	 * @param string $relModule
	 * @param string $perType
	 * @param boolean $varNameArr
	 */
	public static function populateRelatedSharingPrivileges($enttype, $userid, $module, $relmodule, $pertype, $var_name_arr = false)
	{
		$adb = \PearDatabase::getInstance();
		$tabid = Module::getModuleId($module);
		$reltabid = Module::getModuleId($relmodule);

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
}
