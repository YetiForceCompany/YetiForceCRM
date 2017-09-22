<?php
namespace App;

/**
 * Create user privileges file class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class CreateUserPrivilegesFile
{

	/** Creates a file with all the user, user-role,user-profile, user-groups informations
	 * @param int $userId
	 * @returns User_Privileges_Userid file under the User_Privileges Directory
	 */
	public static function createUserPrivilegesfile($userId)
	{
		$handle = @fopen(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges/user_privileges_' . $userId . '.php', 'w+');

		if ($handle) {
			$newBuf = '';
			$newBuf .= '<?php\n';
			$userFocus = CRMEntity::getInstance('Users');
			$userFocus->retrieveEntityInfo($userId, 'Users');
			$userInfo = [];
			$userFocus->column_fields['id'] = '';
			$userFocus->id = $userId;
			foreach ($userFocus->column_fields as $field => $valueIter) {
				if (isset($userFocus->$field)) {
					$userInfo[$field] = $userFocus->$field;
				}
			}
			if ($userFocus->is_admin == 'on') {
				$newBuf .= '\$is_admin=true;\n';
				$newBuf .= '\$user_info=' . Utils::varExport($userInfo) . ';\n';
			} else {
				$newBuf .= '\$is_admin=false;\n';

				$globalPermissionArr = PrivilegeUtil::getCombinedUserGlobalPermissions($userId);
				$tabsPermissionArr = PrivilegeUtil::getCombinedUserTabsPermissions($userId);
				$actionPermissionArr = getCombinedUserActionPermissions($userId);
				$userRole = PrivilegeUtil::getRoleByUsers($userId);
				$userRoleInfo = PrivilegeUtil::getRoleDetail($userRole);
				$userRoleParent = $userRoleInfo['parentrole'];
				$subRoles = PrivilegeUtil::getRoleSubordinates($userRole);
				$subRoleAndUsers = getSubordinateRoleAndUsers($userRole);
				$parentRoles = PrivilegeUtil::getParentRole($userRole);
				$newBuf .= "\$current_user_roles='" . $userRole . "';\n";
				$newBuf .= "\$current_user_parent_role_seq='" . $userRoleParent . "';\n";
				$newBuf .= '\$current_user_profiles=' . Utils::varExport(PrivilegeUtil::getProfilesByRole($userRole)) . ';\n';
				$newBuf .= '\$profileGlobalPermission=' . Utils::varExport($globalPermissionArr) . ';\n';
				$newBuf .= '\$profileTabsPermission=' . Utils::varExport($tabsPermissionArr) . ';\n';
				$newBuf .= '\$profileActionPermission=' . Utils::varExport($actionPermissionArr) . ';\n';
				$newBuf .= '\$current_user_groups=' . Utils::varExport(PrivilegeUtil::getAllGroupsByUser($userId)) . ';\n';
				$newBuf .= '\$subordinate_roles=' . Utils::varExport($subRoles) . ';\n';
				$newBuf .= '\$parent_roles=' . Utils::varExport($parentRoles) . ';\n';
				$newBuf .= '\$subordinate_roles_users=' . Utils::varExport($subRoleAndUsers) . ';\n';
				$newBuf .= '\$user_info=' . Utils::varExport($userInfo) . ';\n';
			}
			fputs($handle, $newBuf);
			fclose($handle);
			PrivilegeFile::createUserPrivilegesFile($userId);
			\Users_Privileges_Model::clearCache($userId);
			User::clearCache($userId);
		}
	}

	/**
	 * Creates a file with all the organization default sharing permissions 
	 * and custom sharing permissins specific for the specified user. 
	 * In this file the information of the other users whose data is shared with the specified user is stored.
	 * @param int $userId
	 * @returns sharing_privileges_userid file under the user_privileges directory
	 */
	public static function createUserSharingPrivilegesfile($userId)
	{
		\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/user_privileges_' . $userId . '.php');
		require('user_privileges/user_privileges_' . $userId . '.php');
		$handle = @fopen(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges/sharing_privileges_' . $userId . '.php', "w+");

		if ($handle) {
			$newBuf = '<?php\n';
			$userFocus = \CRMEntity::getInstance('Users');
			$userFocus->retrieveEntityInfo($userId, 'Users');
			if ($userFocus->is_admin == 'on') {
				fputs($handle, $newBuf);
				fclose($handle);
				return;
			} else {
				$sharingPrivileges = [];
//Constructig the Default Org Share Array
				$defOrgShare = PrivilegeUtil::getAllDefaultSharingAction();
				$newBuf .= '\$defaultOrgSharingPermission=' . Utils::varExport($defOrgShare) . ';\n';
				$sharingPrivileges['defOrgShare'] = $defOrgShare;

				$relatedModuleShare = PrivilegeUtil::getDatashareRelatedModules();
				$newBuf .= '\$related_module_share=' . Utils::varExport($relatedModuleShare) . ';\n';
				$sharingPrivileges['relatedModuleShare'] = $relatedModuleShare;
//Constructing Account Sharing Rules
				$accountSharePerArray = PrivilegeUtil::getUserModuleSharingObjects('Accounts', $userId, $defOrgShare, $currentUserRoles, $parentRoles, $currentUserGroups);
				$accountShareReadPer = $accountSharePerArray['read'];
				$accountShareWritePer = $accountSharePerArray['write'];
				$accountSharingruleMembers = $accountSharePerArray['sharingrules'];
				$newBuf .= "\$Accounts_share_read_permission=array('ROLE'=>" . Utils::varExport($accountShareReadPer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accountShareReadPer['GROUP']) . ');\n';
				$newBuf .= "\$Accounts_share_write_permission=array('ROLE'=>" . Utils::varExport($accountShareWritePer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accountShareWritePer['GROUP']) . ');\n';
				$sharingPrivileges['permission']['Accounts'] = ['read' => $accountShareReadPer, 'write' => $accountShareWritePer];
//Constructing Contact Sharing Rules
				$newBuf .= "\$Contacts_share_read_permission=array('ROLE'=>" . Utils::varExport($accountShareReadPer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accountShareReadPer['GROUP']) . ');\n';
				$newBuf .= "\$Contacts_share_write_permission=array('ROLE'=>" . Utils::varExport($accountShareWritePer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accountShareWritePer['GROUP']) . ');\n';
				$sharingPrivileges['permission']['Contacts'] = ['read' => $accountShareReadPer, 'write' => $accountShareWritePer];

//Constructing the Account Ticket Related Module Sharing Array
				$acctRelatedTkt = self::getRelatedModuleSharingArray('Accounts', 'HelpDesk', $accountSharingruleMembers, $accountShareReadPer, $accountShareWritePer, $defOrgShare);
				$accTktShareReadPer = $acctRelatedTkt['read'];
				$accTktShareWritePer = $acctRelatedTkt['write'];
				$newBuf .= "\$Accounts_HelpDesk_share_read_permission=array('ROLE'=>" . Utils::varExport($accTktShareReadPer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accTktShareReadPer['GROUP']) . ');\n';
				$newBuf .= "\$Accounts_HelpDesk_share_write_permission=array('ROLE'=>" . Utils::varExport($accTktShareWritePer['ROLE']) . ",'GROUP'=>" . Utils::varExport($accTktShareWritePer['GROUP']) . ');\n';
				$sharingPrivileges['permission']['Accounts_HelpDesk'] = ['read' => $accTktShareReadPer, 'write' => $accTktShareWritePer];

				$customModules = Module::getSharingModuleList(['Accounts', 'Contacts']);
				foreach ($customModules as &$moduleName) {
					$modSharePermArray = PrivilegeUtil::getUserModuleSharingObjects($moduleName, $userId, $defOrgShare, $currentUserRoles, $parentRoles, $currentUserGroups);

					$modShareReadPerm = $modSharePermArray['read'];
					$modShareWritePerm = $modSharePermArray['write'];
					$newBuf .= '$' . $moduleName . "_share_read_permission=['ROLE'=>" .
						Utils::varExport($modShareReadPerm['ROLE']) . ",'GROUP'=>" .
						Utils::varExport($modShareReadPerm['GROUP']) . '];\n';
					$newBuf .= '$' . $moduleName . "_share_write_permission=['ROLE'=>" .
						Utils::varExport($modShareWritePerm['ROLE']) . ",'GROUP'=>" .
						Utils::varExport($modShareWritePerm['GROUP']) . '];\n';

					$sharingPrivileges['permission'][$moduleName] = ['read' => $modShareReadPerm, 'write' => $modShareWritePerm];
				}
				$newBuf .= 'return ' . Utils::varExport($sharingPrivileges) . ';\n';
// END
				fputs($handle, $newBuf);
				fclose($handle);

//Populating Temp Tables
				self::populateSharingTmpTables($userId);
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
	public static function getRelatedModuleSharingArray($parMod, $shareMod, $modSharingRuleMembers, $modShareReadPer, $modShareWritePer, $defOrgShare)
	{

		$adb = \PearDatabase::getInstance();
		$modShareReadPermission = [];
		$modShareWritePermission = [];

		$modShareReadPermission['ROLE'] = [];
		$modShareWritePermission['ROLE'] = [];
		$modShareReadPermission['GROUP'] = [];
		$modShareWritePermission['GROUP'] = [];

		$parModId = Module::getModuleId($parMod);
		$shareModId = Module::getModuleId($shareMod);

		if ($defOrgShare[$shareModId] == 3 || $defOrgShare[$shareModId] == 0) {
			$roleReadPer = [];
			$roleWritePer = [];
			$grpReadPer = [];
			$grpWitePer = [];

			foreach ($modSharingRuleMembers as $sharingid => $sharingInfoArr) {
				$query = 'select vtiger_datashare_relatedmodule_permission.* from vtiger_datashare_relatedmodule_permission inner join vtiger_datashare_relatedmodules on vtiger_datashare_relatedmodules.datashare_relatedmodule_id=vtiger_datashare_relatedmodule_permission.datashare_relatedmodule_id where vtiger_datashare_relatedmodule_permission.shareid=? and vtiger_datashare_relatedmodules.tabid=? and vtiger_datashare_relatedmodules.relatedto_tabid=?';
				$result = $adb->pquery($query, array($sharingid, $parModId, $shareModId));
				$sharePermission = $adb->queryResult($result, 0, 'permission');

				foreach ($sharingInfoArr as $shareType => $shareEntArr) {
					foreach ($shareEntArr as $key => $shareEntId) {
						if ($shareType === 'ROLE') {
							if ($sharePermission == 1) {
								if ($defOrgShare[$shareModId] == 3) {
									if (!array_key_exists($shareEntId, $roleReadPer)) {
										if (array_key_exists($shareEntId, $modShareReadPer['ROLE'])) {
											$shareRoleUsers = $modShareReadPer['ROLE'][$shareEntId];
										} elseif (array_key_exists($shareEntId, $modShareWritePer['ROLE'])) {
											$shareRoleUsers = $modShareWritePer['ROLE'][$shareEntId];
										} else {

											$shareRoleUsers = PrivilegeUtil::getUsersByRole($shareEntId);
										}

										$roleReadPer[$shareEntId] = $shareRoleUsers;
									}
								}
								if (!array_key_exists($shareEntId, $roleWritePer)) {
									if (array_key_exists($shareEntId, $modShareReadPer['ROLE'])) {
										$shareRoleUsers = $modShareReadPer['ROLE'][$shareEntId];
									} elseif (array_key_exists($shareEntId, $modShareWritePer['ROLE'])) {
										$shareRoleUsers = $modShareWritePer['ROLE'][$shareEntId];
									} else {

										$shareRoleUsers = PrivilegeUtil::getUsersByRole($shareEntId);
									}

									$roleWritePer[$shareEntId] = $shareRoleUsers;
								}
							} elseif ($sharePermission == 0 && $defOrgShare[$shareModId] == 3) {
								if (!array_key_exists($shareEntId, $roleReadPer)) {
									if (array_key_exists($shareEntId, $modShareReadPer['ROLE'])) {
										$shareRoleUsers = $modShareReadPer['ROLE'][$shareEntId];
									} elseif (array_key_exists($shareEntId, $modShareWritePer['ROLE'])) {
										$shareRoleUsers = $modShareWritePer['ROLE'][$shareEntId];
									} else {

										$shareRoleUsers = PrivilegeUtil::getUsersByRole($shareEntId);
									}

									$roleReadPer[$shareEntId] = $shareRoleUsers;
								}
							}
						} elseif ($shareType === 'GROUP') {
							if ($sharePermission == 1) {
								if ($defOrgShare[$shareModId] == 3) {

									if (!array_key_exists($shareEntId, $grpReadPer)) {
										if (array_key_exists($shareEntId, $modShareReadPer['GROUP'])) {
											$shareGrpUsers = $modShareReadPer['GROUP'][$shareEntId];
										} elseif (array_key_exists($shareEntId, $modShareWritePer['GROUP'])) {
											$shareGrpUsers = $modShareWritePer['GROUP'][$shareEntId];
										} else {
											$usersByGroup = PrivilegeUtil::getUsersByGroup($shareEntId, true);
											$shareGrpUsers = $usersByGroup['users'];
											foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
												if (!array_key_exists($subgrpid, $grpReadPer)) {
													$grpReadPer[$subgrpid] = $subgrpusers;
												}
											}
										}

										$grpReadPer[$shareEntId] = $shareGrpUsers;
									}
								}
								if (!array_key_exists($shareEntId, $grpWitePer)) {
									if (!array_key_exists($shareEntId, $grpWitePer)) {
										if (array_key_exists($shareEntId, $modShareReadPer['GROUP'])) {
											$shareGrpUsers = $modShareReadPer['GROUP'][$shareEntId];
										} elseif (array_key_exists($shareEntId, $modShareWritePer['GROUP'])) {
											$shareGrpUsers = $modShareWritePer['GROUP'][$shareEntId];
										} else {
											$usersByGroup = PrivilegeUtil::getUsersByGroup($shareEntId, true);
											$shareGrpUsers = $usersByGroup['users'];
											foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
												if (!array_key_exists($subgrpid, $grpWitePer)) {
													$grpWitePer[$subgrpid] = $subgrpusers;
												}
											}
										}

										$grpWitePer[$shareEntId] = $shareGrpUsers;
									}
								}
							} elseif ($sharePermission == 0 && $defOrgShare[$shareModId] == 3) {
								if (!array_key_exists($shareEntId, $grpReadPer)) {
									if (array_key_exists($shareEntId, $modShareReadPer['GROUP'])) {
										$shareGrpUsers = $modShareReadPer['GROUP'][$shareEntId];
									} elseif (array_key_exists($shareEntId, $modShareWritePer['GROUP'])) {
										$shareGrpUsers = $modShareWritePer['GROUP'][$shareEntId];
									} else {
										$usersByGroup = PrivilegeUtil::getUsersByGroup($shareEntId, true);
										$shareGrpUsers = $usersByGroup['users'];
										foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
											if (!array_key_exists($subgrpid, $grpReadPer)) {
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
			$modShareWritePermission['GROUP'] = $grpWitePer;
		}
		$relatedModSharingPermission['read'] = $modShareReadPermission;
		$relatedModSharingPermission['write'] = $modShareWritePermission;
		return $relatedModSharingPermission;
	}

	/** Function to populate the read/wirte Sharing permissions data of user/groups for the specified user into the database
	 * @param int $userId
	 */
	public static function populateSharingTmpTables($userId)
	{
		$adb = \PearDatabase::getInstance();
		\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/sharing_privileges_' . $userId . '.php');
		require('user_privileges/sharing_privileges_' . $userId . '.php');
//Deleting from the existing vtiger_tables
		$tableArr = Array('vtiger_tmp_read_user_sharing_per', 'vtiger_tmp_write_user_sharing_per', 'vtiger_tmp_read_group_sharing_per', 'vtiger_tmp_write_group_sharing_per', 'vtiger_tmp_read_user_rel_sharing_per', 'vtiger_tmp_write_user_rel_sharing_per', 'vtiger_tmp_read_group_rel_sharing_per', 'vtiger_tmp_write_group_rel_sharing_per');
		foreach ($tableArr as $tabName) {
			$adb->delete($tabName, 'userid = ?', [$userId]);
		}

// Look up for modules for which sharing access is enabled.
		$modules = \vtlib\Functions::getAllModules(true, true, 0, false, 0);
		$sharingArray = array_column($modules, 'name');
		foreach ($sharingArray as $module) {
			$moduleSharingReadPermvar = $module . '_share_read_permission';
			$moduleSharingWritePermvar = $module . '_share_write_permission';

			self::populateSharingPrivileges('USER', $userId, $module, 'read', $$moduleSharingReadPermvar);
			self::populateSharingPrivileges('USER', $userId, $module, 'write', $$moduleSharingWritePermvar);
			self::populateSharingPrivileges('GROUP', $userId, $module, 'read', $$moduleSharingReadPermvar);
			self::populateSharingPrivileges('GROUP', $userId, $module, 'write', $$moduleSharingWritePermvar);
		}
//Populating Values into the temp related sharing tables
		foreach ($relatedModuleShare as $relTabId => $tabidArr) {
			$relTabName = Module::getModuleName($relTabId);
			if (!empty($relTabName)) {
				foreach ($tabidArr as $taId) {
					$tabName = Module::getModuleName($taId);

					$relmoduleSharingReadPermvar = $tabName . '_' . $relTabName . '_share_read_permission';
					$relmoduleSharingWritePermvar = $tabName . '_' . $relTabName . '_share_write_permission';

					self::populateRelatedSharingPrivileges('USER', $userId, $tabName, $relTabName, 'read', $$relmoduleSharingReadPermvar);
					self::populateRelatedSharingPrivileges('USER', $userId, $tabName, $relTabName, 'write', $$relmoduleSharingWritePermvar);
					self::populateRelatedSharingPrivileges('GROUP', $userId, $tabName, $relTabName, 'read', $$relmoduleSharingReadPermvar);
					self::populateRelatedSharingPrivileges('GROUP', $userId, $tabName, $relTabName, 'write', $$relmoduleSharingWritePermvar);
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
	public static function populateSharingPrivileges($entType, $userId, $module, $perType, $varNameArr = false)
	{
		$adb = \PearDatabase::getInstance();
		$tabId = Module::getModuleId($module);

		if (!$varNameArr) {
			\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/sharing_privileges_' . $userId . '.php');
			require('user_privileges/sharing_privileges_' . $userId . '.php');
		}

		if ($entType === 'USER') {
			if ($perType === 'read') {
				$tableName = 'vtiger_tmp_read_user_sharing_per';
				$varName = $module . '_share_read_permission';
			} elseif ($perType === 'write') {
				$tableName = 'vtiger_tmp_write_user_sharing_per';
				$varName = $module . '_share_write_permission';
			}
// Lookup for the variable if not set through function argument
			if (!$varNameArr) {
				$varNameArr = $$varName;
			}
			$userArr = [];
			if (sizeof($varNameArr['ROLE']) > 0) {
				foreach ($varNameArr['ROLE'] as $roleId => $roleUsers) {

					foreach ($roleUsers as $usersId) {
						if (!in_array($usersId, $userArr)) {
							$query = 'insert into' . $tableName . 'values(?,?,?)';
							$adb->pquery($query, array($userId, $tabId, $usersId));
							$userArr[] = $usersId;
						}
					}
				}
			}
			if (sizeof($varNameArr['GROUP']) > 0) {
				foreach ($varNameArr['GROUP'] as $grpId => $grpUsers) {
					foreach ($grpUsers as $usersId) {
						if (!in_array($usersId, $userArr)) {
							$query = 'insert into' . $tableName . 'values(?,?,?)';
							$adb->pquery($query, array($userId, $tabId, $usersId));
							$userArr[] = $usersId;
						}
					}
				}
			}
		} elseif ($entType === 'GROUP') {
			if ($perType === 'read') {
				$tableName = 'vtiger_tmp_read_group_sharing_per';
				$varName = $module . '_share_read_permission';
			} elseif ($perType === 'write') {
				$tableName = 'vtiger_tmp_write_group_sharing_per';
				$varName = $module . '_share_write_permission';
			}
// Lookup for the variable if not set through function argument
			if (!$varNameArr) {
				$varNameArr = $$varName;
			}
			$grpArr = [];
			if (sizeof($varNameArr['GROUP']) > 0) {

				foreach ($varNameArr['GROUP'] as $grpId => $grpUsers) {
					if (!in_array($grpId, $grpArr)) {
						$query = 'insert into' . $tableName . 'values(?,?,?)';
						$adb->pquery($query, array($userId, $tabId, $grpId));
						$grpArr[] = $grpId;
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
	public static function populateRelatedSharingPrivileges($entType, $userId, $module, $relModule, $perType, $varNameArr = false)
	{
		$adb = \PearDatabase::getInstance();
		$tabId = Module::getModuleId($module);
		$relTabId = Module::getModuleId($relModule);

		if (!$varNameArr) {
			\vtlib\Deprecated::checkFileAccessForInclusion('user_privileges/sharing_privileges_' . $userId . '.php');
			require('user_privileges/sharing_privileges_' . $userId . '.php');
		}

		if ($entType === 'USER') {
			if ($perType === 'read') {
				$tableName = 'vtiger_tmp_read_user_rel_sharing_per';
				$varName = $module . '_' . $relModule . '_share_read_permission';
			} elseif ($perType === 'write') {
				$tableName = 'vtiger_tmp_write_user_rel_sharing_per';
				$varName = $module . '_' . $relModule . '_share_write_permission';
			}
// Lookup for the variable if not set through function argument
			if (!$varNameArr) {
				$varNameArr = $$varName;
			}
			$userArr = [];
			if (sizeof($varNameArr['ROLE']) > 0) {
				foreach ($varNameArr['ROLE'] as $roleId => $roleUsers) {

					foreach ($roleUsers as $usersId) {
						if (!in_array($usersId, $userArr)) {
							$query = 'insert into' . $tableName . 'values(?,?,?,?)';
							$adb->pquery($query, array($userId, $tabId, $relTabId, $usersId));
							$userArr[] = $usersId;
						}
					}
				}
			}
			if (sizeof($varNameArr['GROUP']) > 0) {
				foreach ($varNameArr['GROUP'] as $grpid => $grpusers) {
					foreach ($grpusers as $usersId) {
						if (!in_array($usersId, $userArr)) {
							$query = 'insert into' . $tableName . 'values(?,?,?,?)';
							$adb->pquery($query, array($userId, $tabId, $relTabId, $usersId));
							$userArr[] = $usersId;
						}
					}
				}
			}
		} elseif ($entType === 'GROUP') {
			if ($perType === 'read') {
				$tableName = 'vtiger_tmp_read_group_rel_sharing_per';
				$varName = $module . '_' . $relModule . '_share_read_permission';
			} elseif ($perType === 'write') {
				$tableName = 'vtiger_tmp_write_group_rel_sharing_per';
				$varName = $module . '_' . $relModule . '_share_write_permission';
			}
// Lookup for the variable if not set through function argument
			if (!$varNameArr) {
				$varNameArr = $$varName;
			}
			$grpArr = [];
			if (sizeof($varNameArr['GROUP']) > 0) {

				foreach ($varNameArr['GROUP'] as $grpid => $grpusers) {
					if (!in_array($grpid, $grpArr)) {
						$query = 'insert into' . $tableName . 'values(?,?,?,?)';
						$adb->pquery($query, array($userId, $tabId, $relTabId, $grpid));
						$grpArr[] = $grpid;
					}
				}
			}
		}
	}
}
