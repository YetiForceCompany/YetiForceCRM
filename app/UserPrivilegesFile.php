<?php

namespace App;

/**
 * Create user privileges file class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class UserPrivilegesFile
{
	/**
	 * Function to recalculate the Sharing Rules for all the vtiger_users
	 * This function will recalculate all the sharing rules for all the vtiger_users in the Organization and will write them in flat vtiger_files.
	 *
	 * @return int
	 */
	public static function recalculateAll(): int
	{
		$userIds = (new Db\Query())->select(['id'])->from('vtiger_users')->where(['deleted' => 0])->column();
		foreach ($userIds as $id) {
			static::createUserPrivilegesfile($id);
			static::createUserSharingPrivilegesfile($id);
		}
		return \count($userIds);
	}

	/**
	 * Creates a file with all the user, user-role,user-profile, user-groups informations.
	 *
	 * @param int $userid
	 * @returns User_Privileges_Userid file under the User_Privileges Directory
	 */
	public static function createUserPrivilegesfile($userid)
	{
		$fileUserPrivileges = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'user_privileges/user_privileges_' . $userid . '.php';
		$handle = fopen($fileUserPrivileges, 'w+');
		if ($handle) {
			$newBuf = '';
			$newBuf .= "<?php\n";
			$userFocus = \CRMEntity::getInstance('Users');
			$userFocus->retrieveEntityInfo($userid, 'Users');
			$userInfo = [];
			$userFocus->column_fields['id'] = '';
			$userFocus->id = $userid;
			foreach ($userFocus->column_fields as $field => $value) {
				if (isset($userFocus->{$field})) {
					if ('currency_symbol' === $field || $field === 'imagename'|| $field === 'othereventduration') {
						$userInfo[$field] = $userFocus->{$field};
					} else {
						$userInfo[$field] = is_numeric($userFocus->{$field}) ? $userFocus->{$field} : \App\Purifier::encodeHtml($userFocus->{$field});
					}
				}
			}
			if ('on' == $userFocus->is_admin) {
				$newBuf .= "\$is_admin=true;\n";
				$newBuf .= '$user_info=' . Utils::varExport($userInfo) . ";\n";
			} else {
				$newBuf .= "\$is_admin=false;\n";
				$globalPermissionArr = PrivilegeUtil::getCombinedUserGlobalPermissions($userid);
				$tabsPermissionArr = PrivilegeUtil::getCombinedUserModulesPermissions($userid);
				$actionPermissionArr = PrivilegeUtil::getCombinedUserActionsPermissions($userid);
				$userRole = PrivilegeUtil::getRoleByUsers($userid);
				$userRoleParent = PrivilegeUtil::getRoleDetail($userRole)['parentrole'];
				$subRoles = PrivilegeUtil::getRoleSubordinates($userRole);
				$subRoleAndUsers = [];
				foreach ($subRoles as $subRoleId) {
					$subRoleAndUsers[$subRoleId] = \App\PrivilegeUtil::getUsersNameByRole($subRoleId);
				}
				$parentRoles = PrivilegeUtil::getParentRole($userRole);
				$newBuf .= "\$current_user_roles='" . $userRole . "';\n";
				$newBuf .= "\$current_user_parent_role_seq='" . $userRoleParent . "';\n";
				$newBuf .= '$current_user_profiles=' . Utils::varExport(PrivilegeUtil::getProfilesByRole($userRole)) . ";\n";
				$newBuf .= '$profileGlobalPermission=' . Utils::varExport($globalPermissionArr) . ";\n";
				$newBuf .= '$profileTabsPermission=' . Utils::varExport($tabsPermissionArr) . ";\n";
				$newBuf .= '$profileActionPermission=' . Utils::varExport($actionPermissionArr) . ";\n";
				$newBuf .= '$current_user_groups=' . Utils::varExport(PrivilegeUtil::getAllGroupsByUser($userid)) . ";\n";
				$newBuf .= '$subordinate_roles=' . Utils::varExport($subRoles) . ";\n";
				$newBuf .= '$parent_roles=' . Utils::varExport($parentRoles) . ";\n";
				$newBuf .= '$subordinate_roles_users=' . Utils::varExport($subRoleAndUsers) . ";\n";
				$newBuf .= '$user_info=' . Utils::varExport($userInfo) . ";\n";
			}
			fwrite($handle, $newBuf);
			fclose($handle);
			PrivilegeFile::createUserPrivilegesFile($userid);
			\Users_Privileges_Model::clearCache($userid);
			User::clearCache($userid);
			\App\Cache::resetFileCache($fileUserPrivileges);
		}
	}

	/**
	 * Creates a file with all the organization default sharing permissions
	 * and custom sharing permissins specific for the specified user.
	 * In this file the information of the other users whose data is shared with the specified user is stored.
	 *
	 * @param int $userid
	 * @returns sharing_privileges_userid file under the user_privileges directory
	 */
	public static function createUserSharingPrivilegesfile($userid)
	{
		\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/user_privileges_' . $userid . '.php');
		require 'user_privileges/user_privileges_' . $userid . '.php';
		$fileUserSharingPrivileges = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'user_privileges/sharing_privileges_' . $userid . '.php';
		$handle = fopen($fileUserSharingPrivileges, 'w+');
		if ($handle) {
			$newBuf = "<?php\n";
			$userFocus = \CRMEntity::getInstance('Users');
			$userFocus->retrieveEntityInfo($userid, 'Users');
			if ('on' == $userFocus->is_admin) {
				fwrite($handle, $newBuf);
				fclose($handle);
			} else {
				$sharingPrivileges = [];
				//Constructig the Default Org Share Array
				$defOrgShare = PrivilegeUtil::getAllDefaultSharingAction();
				$newBuf .= '$defaultOrgSharingPermission=' . Utils::varExport($defOrgShare) . ";\n";
				$sharingPrivileges['defOrgShare'] = $defOrgShare;
				$relatedModuleShare = PrivilegeUtil::getDatashareRelatedModules();
				$newBuf .= '$related_module_share=' . Utils::varExport($relatedModuleShare) . ";\n";
				$sharingPrivileges['relatedModuleShare'] = $relatedModuleShare;
				//Constructing Account Sharing Rules
				$accountSharePerArray = PrivilegeUtil::getUserModuleSharingObjects('Accounts', $userid, $defOrgShare, $current_user_roles, $parent_roles, $current_user_groups);
				$accountShareReadPer = $accountSharePerArray['read'];
				$accountShareWritePer = $accountSharePerArray['write'];
				$accountSharingruleMembers = $accountSharePerArray['sharingrules'];
				$newBuf .= "\$Accounts_share_read_permission=array('ROLE'=>" . Utils::varExport($accountShareReadPer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accountShareReadPer['GROUP']) . ");\n";
				$newBuf .= "\$Accounts_share_write_permission=array('ROLE'=>" . Utils::varExport($accountShareWritePer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accountShareWritePer['GROUP']) . ");\n";
				$sharingPrivileges['permission']['Accounts'] = ['read' => $accountShareReadPer, 'write' => $accountShareWritePer];
				//Constructing Contact Sharing Rules
				$newBuf .= "\$Contacts_share_read_permission=array('ROLE'=>" . Utils::varExport($accountShareReadPer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accountShareReadPer['GROUP']) . ");\n";
				$newBuf .= "\$Contacts_share_write_permission=array('ROLE'=>" . Utils::varExport($accountShareWritePer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accountShareWritePer['GROUP']) . ");\n";
				$sharingPrivileges['permission']['Contacts'] = ['read' => $accountShareReadPer, 'write' => $accountShareWritePer];
				//Constructing the Account Ticket Related Module Sharing Array
				$acctRelatedTkt = static::getRelatedModuleSharingArray('Accounts', 'HelpDesk', $accountSharingruleMembers, $accountShareReadPer, $accountShareWritePer, $defOrgShare);
				$accTktShareReadPer = $acctRelatedTkt['read'];
				$accTktShareWriteer = $acctRelatedTkt['write'];
				$newBuf .= "\$Accounts_HelpDesk_share_read_permission=array('ROLE'=>" . Utils::varExport($accTktShareReadPer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accTktShareReadPer['GROUP']) . ");\n";
				$newBuf .= "\$Accounts_HelpDesk_share_write_permission=array('ROLE'=>" . Utils::varExport($accTktShareWriteer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accTktShareWriteer['GROUP']) . ");\n";
				$sharingPrivileges['permission']['Accounts_HelpDesk'] = ['read' => $accTktShareReadPer, 'write' => $accTktShareWriteer];
				$customModules = Module::getSharingModuleList(['Accounts', 'Contacts']);
				foreach ($customModules as $moduleName) {
					$modSharePermArray = PrivilegeUtil::getUserModuleSharingObjects($moduleName, $userid, $defOrgShare, $current_user_roles, $parent_roles, $current_user_groups);
					$modShareReadPerm = $modSharePermArray['read'];
					$modShareWritePerm = $modSharePermArray['write'];
					$newBuf .= '$' . $moduleName . "_share_read_permission=['ROLE'=>" .
						Utils::varExport($modShareReadPerm['ROLE']) . ",'GROUP'=>" .
						Utils::varExport($modShareReadPerm['GROUP']) . "];\n";
					$newBuf .= '$' . $moduleName . "_share_write_permission=['ROLE'=>" .
						Utils::varExport($modShareWritePerm['ROLE']) . ",'GROUP'=>" .
						Utils::varExport($modShareWritePerm['GROUP']) . "];\n";

					$sharingPrivileges['permission'][$moduleName] = ['read' => $modShareReadPerm, 'write' => $modShareWritePerm];
				}
				$newBuf .= 'return ' . Utils::varExport($sharingPrivileges) . ";\n";
				// END
				fwrite($handle, $newBuf);
				fclose($handle);
				//Populating Temp Tables
				\App\Cache::resetFileCache($fileUserSharingPrivileges);
				static::populateSharingtmptables($userid);
				User::clearCache($userid);
			}
		}
	}

	/**
	 * Gives an array which contains the information for what all roles,
	 * groups and user's related module data that is to be shared  for the specified parent module and shared module.
	 *
	 * @param string $par_mod
	 * @param string $share_mod
	 * @param array  $mod_sharingrule_members
	 * @param array  $mod_share_read_per
	 * @param array  $mod_share_write_per
	 * @param array  $def_org_share
	 *
	 * @return array
	 */
	public static function getRelatedModuleSharingArray($par_mod, $share_mod, $mod_sharingrule_members, $mod_share_read_per, $mod_share_write_per, $def_org_share)
	{
		$relatedModSharingPermission = [];
		$modShareReadPermission = [];
		$modShareWritePermission = [];
		$modShareReadPermission['ROLE'] = [];
		$modShareWritePermission['ROLE'] = [];
		$modShareReadPermission['GROUP'] = [];
		$modShareWritePermission['GROUP'] = [];
		$parModId = Module::getModuleId($par_mod);
		$shareModId = Module::getModuleId($share_mod);
		if (3 == $def_org_share[$shareModId] || 0 == $def_org_share[$shareModId]) {
			$roleReadPer = [];
			$roleWritePer = [];
			$grpReadPer = [];
			$grpWritePer = [];
			foreach ($mod_sharingrule_members as $sharingid => $sharingInfoArr) {
				$sharePermission = (new Db\Query())->select(['vtiger_datashare_relatedmodule_permission.permission'])
					->from('vtiger_datashare_relatedmodule_permission')
					->innerJoin('vtiger_datashare_relatedmodules', 'vtiger_datashare_relatedmodules.datashare_relatedmodule_id = vtiger_datashare_relatedmodule_permission.datashare_relatedmodule_id')
					->where([
						'vtiger_datashare_relatedmodule_permission.shareid' => $sharingid,
						'vtiger_datashare_relatedmodules.tabid' => $parModId,
						'vtiger_datashare_relatedmodules.relatedto_tabid' => $shareModId,
					])->scalar();
				foreach ($sharingInfoArr as $shareType => $shareEntArr) {
					foreach ($shareEntArr as $shareEntId) {
						if ('ROLE' == $shareType) {
							if (1 == $sharePermission) {
								if (3 == $def_org_share[$shareModId] && !isset($roleReadPer[$shareEntId])) {
									if (isset($mod_share_read_per['ROLE'][$shareEntId])) {
										$shareRoleUsers = $mod_share_read_per['ROLE'][$shareEntId];
									} elseif (isset($mod_share_write_per['ROLE'][$shareEntId])) {
										$shareRoleUsers = $mod_share_write_per['ROLE'][$shareEntId];
									} else {
										$shareRoleUsers = PrivilegeUtil::getUsersByRole($shareEntId);
									}
									$roleReadPer[$shareEntId] = $shareRoleUsers;
								}
								if (!isset($roleWritePer[$shareEntId])) {
									if (isset($mod_share_read_per['ROLE'][$shareEntId])) {
										$shareRoleUsers = $mod_share_read_per['ROLE'][$shareEntId];
									} elseif (isset($mod_share_write_per['ROLE'][$shareEntId])) {
										$shareRoleUsers = $mod_share_write_per['ROLE'][$shareEntId];
									} else {
										$shareRoleUsers = PrivilegeUtil::getUsersByRole($shareEntId);
									}
									$roleWritePer[$shareEntId] = $shareRoleUsers;
								}
							} elseif (0 == $sharePermission && 3 == $def_org_share[$shareModId]) {
								if (!isset($roleReadPer[$shareEntId])) {
									if (isset($mod_share_read_per['ROLE'][$shareEntId])) {
										$shareRoleUsers = $mod_share_read_per['ROLE'][$shareEntId];
									} elseif (isset($mod_share_write_per['ROLE'][$shareEntId])) {
										$shareRoleUsers = $mod_share_write_per['ROLE'][$shareEntId];
									} else {
										$shareRoleUsers = PrivilegeUtil::getUsersByRole($shareEntId);
									}
									$roleReadPer[$shareEntId] = $shareRoleUsers;
								}
							}
						} elseif ('GROUP' == $shareType) {
							if (1 == $sharePermission) {
								if (3 == $def_org_share[$shareModId] && !isset($grpReadPer[$shareEntId])) {
									if (isset($mod_share_read_per['GROUP'][$shareEntId])) {
										$shareGrpUsers = $mod_share_read_per['GROUP'][$shareEntId];
									} elseif (isset($mod_share_write_per['GROUP'][$shareEntId])) {
										$shareGrpUsers = $mod_share_write_per['GROUP'][$shareEntId];
									} else {
										$usersByGroup = PrivilegeUtil::getUsersByGroup($shareEntId, true);
										$shareGrpUsers = $usersByGroup['users'];
										foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
											if (!isset($grpReadPer[$subgrpid])) {
												$grpReadPer[$subgrpid] = $subgrpusers;
											}
										}
									}
									$grpReadPer[$shareEntId] = $shareGrpUsers;
								}
								if (!isset($grpWritePer[$shareEntId])) {
									if (isset($mod_share_read_per['GROUP'][$shareEntId])) {
										$shareGrpUsers = $mod_share_read_per['GROUP'][$shareEntId];
									} elseif (isset($mod_share_write_per['GROUP'][$shareEntId])) {
										$shareGrpUsers = $mod_share_write_per['GROUP'][$shareEntId];
									} else {
										$usersByGroup = PrivilegeUtil::getUsersByGroup($shareEntId, true);
										$shareGrpUsers = $usersByGroup['users'];
										foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
											if (!isset($grpWritePer[$subgrpid])) {
												$grpWritePer[$subgrpid] = $subgrpusers;
											}
										}
									}
									$grpWritePer[$shareEntId] = $shareGrpUsers;
								}
							} elseif (0 == $sharePermission && 3 == $def_org_share[$shareModId]) {
								if (!isset($grpReadPer[$shareEntId])) {
									if (isset($mod_share_read_per['GROUP'][$shareEntId])) {
										$shareGrpUsers = $mod_share_read_per['GROUP'][$shareEntId];
									} elseif (isset($mod_share_write_per['GROUP'][$shareEntId])) {
										$shareGrpUsers = $mod_share_write_per['GROUP'][$shareEntId];
									} else {
										$usersByGroup = PrivilegeUtil::getUsersByGroup($shareEntId, true);
										$shareGrpUsers = $usersByGroup['users'];
										foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
											if (!isset($grpReadPer[$subgrpid])) {
												$grpReadPer[$subgrpid] = $subgrpusers;
											}
										}
									}
									$grpReadPer[$shareEntId] = $shareGrpUsers;
								}
							}
						}
					}
				}
			}
			$modShareReadPermission['ROLE'] = $roleReadPer;
			$modShareWritePermission['ROLE'] = $roleWritePer;
			$modShareReadPermission['GROUP'] = $grpReadPer;
			$modShareWritePermission['GROUP'] = $grpWritePer;
		}
		$relatedModSharingPermission['read'] = $modShareReadPermission;
		$relatedModSharingPermission['write'] = $modShareWritePermission;

		return $relatedModSharingPermission;
	}

	/** Function to populate the read/wirte Sharing permissions data of user/groups for the specified user into the database.
	 * @param int $userid
	 */
	public static function populateSharingtmptables($userid)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/sharing_privileges_' . $userid . '.php');

		require 'user_privileges/sharing_privileges_' . $userid . '.php';
		//Deleting from the existing vtiger_tables
		$tableArr = ['vtiger_tmp_read_user_sharing_per', 'vtiger_tmp_write_user_sharing_per', 'vtiger_tmp_read_group_sharing_per', 'vtiger_tmp_write_group_sharing_per', 'vtiger_tmp_read_user_rel_sharing_per', 'vtiger_tmp_write_user_rel_sharing_per', 'vtiger_tmp_read_group_rel_sharing_per', 'vtiger_tmp_write_group_rel_sharing_per'];
		foreach ($tableArr as $tableName) {
			$dbCommand->delete($tableName, ['userid' => $userid])->execute();
		}
		// Look up for modules for which sharing access is enabled.
		$modules = \vtlib\Functions::getAllModules(true, true, 0, false, 0);
		$sharingArray = array_column($modules, 'name');
		foreach ($sharingArray as $module) {
			$moduleSharingReadPermvar = $module . '_share_read_permission';
			$moduleSharingWritePermvar = $module . '_share_write_permission';
			static::populateSharingPrivileges('USER', $userid, $module, 'read', ${$moduleSharingReadPermvar});
			static::populateSharingPrivileges('USER', $userid, $module, 'write', ${$moduleSharingWritePermvar});
			static::populateSharingPrivileges('GROUP', $userid, $module, 'read', ${$moduleSharingReadPermvar});
			static::populateSharingPrivileges('GROUP', $userid, $module, 'write', ${$moduleSharingWritePermvar});
		}
		//Populating Values into the temp related sharing tables
		foreach ($related_module_share as $relTabId => $tabIdArr) {
			$relTabName = Module::getModuleName($relTabId);
			if (!empty($relTabName)) {
				foreach ($tabIdArr as $tabId) {
					$tabName = Module::getModuleName($tabId);
					$relmoduleSharingReadPermvar = $tabName . '_' . $relTabName . '_share_read_permission';
					$relmoduleSharingWritePermvar = $tabName . '_' . $relTabName . '_share_write_permission';
					static::populateRelatedSharingPrivileges('USER', $userid, $tabName, $relTabName, 'read', ${$relmoduleSharingReadPermvar});
					static::populateRelatedSharingPrivileges('USER', $userid, $tabName, $relTabName, 'write', ${$relmoduleSharingWritePermvar});
					static::populateRelatedSharingPrivileges('GROUP', $userid, $tabName, $relTabName, 'read', ${$relmoduleSharingReadPermvar});
					static::populateRelatedSharingPrivileges('GROUP', $userid, $tabName, $relTabName, 'write', ${$relmoduleSharingWritePermvar});
				}
			}
		}
	}

	/**
	 * Function to populate the read/wirte Sharing permissions data for the specified user into the database.
	 *
	 * @param string $enttype
	 * @param int    $userId
	 * @param string $module
	 * @param string $pertype
	 * @param bool   $varArr
	 */
	public static function populateSharingPrivileges($enttype, $userId, $module, $pertype, $varArr = false)
	{
		$tabId = Module::getModuleId($module);
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (empty($varArr)) {
			return;
		}
		if ('USER' === $enttype) {
			if ('read' === $pertype) {
				$tableName = 'vtiger_tmp_read_user_sharing_per';
			} elseif ('write' === $pertype) {
				$tableName = 'vtiger_tmp_write_user_sharing_per';
			}
			$useArrr = [];
			if (!empty($varArr['ROLE'])) {
				foreach ($varArr['ROLE'] as $roleUsers) {
					foreach ($roleUsers as $sharedUserId) {
						if (!\in_array($sharedUserId, $useArrr) && $userId != $sharedUserId) {
							$dbCommand->insert($tableName, ['userid' => $userId, 'tabid' => $tabId, 'shareduserid' => $sharedUserId])->execute();
							$useArrr[] = $sharedUserId;
						}
					}
				}
			}
			if (!empty($varArr['GROUP'])) {
				foreach ($varArr['GROUP'] as $grpUsers) {
					foreach ($grpUsers as $sharedUserId) {
						if (!\in_array($sharedUserId, $useArrr)) {
							$dbCommand->insert($tableName, ['userid' => $userId, 'tabid' => $tabId, 'shareduserid' => $sharedUserId])->execute();
							$useArrr[] = $sharedUserId;
						}
					}
				}
			}
		} elseif ('GROUP' === $enttype) {
			if ('read' === $pertype) {
				$tableName = 'vtiger_tmp_read_group_sharing_per';
			} elseif ('write' === $pertype) {
				$tableName = 'vtiger_tmp_write_group_sharing_per';
			}
			$grpArr = [];
			if (!empty($varArr['GROUP'])) {
				foreach ($varArr['GROUP'] as $groupId => $grpusers) {
					if (!\in_array($groupId, $grpArr)) {
						$dbCommand->insert($tableName, ['userid' => $userId, 'tabid' => $tabId, 'sharedgroupid' => $groupId])->execute();
						$grpArr[] = $groupId;
					}
				}
			}
		}
	}

	/**
	 * Function to populate the read/wirte Sharing permissions related module data for the specified user into the database.
	 *
	 * @param string $enttype
	 * @param int    $userid
	 * @param string $module
	 * @param string $relmodule
	 * @param string $pertype
	 * @param bool   $varArr
	 * @param mixed  $userId
	 */
	public static function populateRelatedSharingPrivileges($enttype, $userId, $module, $relmodule, $pertype, $varArr = false)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$tabId = Module::getModuleId($module);
		$relTabId = Module::getModuleId($relmodule);
		if (empty($varArr)) {
			return;
		}
		if ('USER' === $enttype) {
			if ('read' === $pertype) {
				$tableName = 'vtiger_tmp_read_user_rel_sharing_per';
			} elseif ('write' === $pertype) {
				$tableName = 'vtiger_tmp_write_user_rel_sharing_per';
			}
			$userArr = [];
			if (!empty($varArr['ROLE'])) {
				foreach ($varArr['ROLE'] as $roleUsers) {
					foreach ($roleUsers as $sharedUserId) {
						if (!\in_array($sharedUserId, $userArr) && $userId != $sharedUserId) {
							$dbCommand->insert($tableName, ['userid' => $userId, 'tabid' => $tabId, 'relatedtabid' => $relTabId, 'shareduserid' => $sharedUserId])->execute();
							$userArr[] = $sharedUserId;
						}
					}
				}
			}
			if (!empty($varArr['GROUP'])) {
				foreach ($varArr['GROUP'] as $grpUsers) {
					foreach ($grpUsers as $sharedUserId) {
						if (!\in_array($sharedUserId, $userArr)) {
							$dbCommand->insert($tableName, ['userid' => $userId, 'tabid' => $tabId, 'relatedtabid' => $relTabId, 'shareduserid' => $sharedUserId])->execute();
							$userArr[] = $sharedUserId;
						}
					}
				}
			}
		} elseif ('GROUP' === $enttype) {
			if ('read' === $pertype) {
				$tableName = 'vtiger_tmp_read_group_rel_sharing_per';
			} elseif ('write' === $pertype) {
				$tableName = 'vtiger_tmp_write_group_rel_sharing_per';
			}
			$grpArr = [];
			if (!empty($varArr['GROUP'])) {
				foreach ($varArr['GROUP'] as $groupId => $grpUsers) {
					if (!\in_array($groupId, $grpArr)) {
						$dbCommand->insert($tableName, ['userid' => $userId, 'tabid' => $tabId, 'relatedtabid' => $relTabId, 'sharedgroupid' => $groupId])->execute();
						$grpArr[] = $groupId;
					}
				}
			}
		}
	}

	/**
	 * Reload user privileges file by multi company id.
	 *
	 * @param int $companyId
	 */
	public static function reloadByMultiCompany(int $companyId)
	{
		foreach (MultiCompany::getUsersByCompany($companyId) as $userId) {
			static::createUserPrivilegesfile($userId);
		}
	}
}
