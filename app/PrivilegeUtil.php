<?php
/**
 * Privilege Util basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

class PrivilegeUtil
{
	/** @var int Allowed group nests */
	public const GROUP_LOOP_LIMIT = 5;

	/** Function to get parent record owner.
	 * @param $tabid    -- tabid :: Type integer
	 * @param $parModId -- parent module id :: Type integer
	 * @param $recordId -- record id :: Type integer
	 * @returns $parentRecOwner -- parentRecOwner:: Type integer
	 */
	public static function getParentRecordOwner($tabid, $parModId, $recordId)
	{
		Log::trace("Entering getParentRecordOwner($tabid,$parModId,$recordId) method ...");
		$parentRecOwner = [];
		$parentTabName = Module::getModuleName($parModId);
		$relTabName = Module::getModuleName($tabid);
		$fnName = 'get' . $relTabName . 'Related' . $parentTabName;
		$entId = static::$fnName($recordId);
		if ('' !== $entId) {
			$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($entId);
			if ($recordMetaData) {
				$ownerId = $recordMetaData['smownerid'];
				$type = \App\Fields\Owner::getType($ownerId);
				$parentRecOwner[$type] = $ownerId;
			}
		}
		Log::trace('Exiting getParentRecordOwner method ...');
		return $parentRecOwner;
	}

	/**
	 * Function return related account with ticket.
	 *
	 * @param int $recordId
	 *
	 * @return int
	 */
	private static function getHelpDeskRelatedAccounts($recordId)
	{
		return (new Db\Query())->select(['parent_id'])->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_troubletickets.parent_id = vtiger_crmentity.crmid')
			->where(['ticketid' => $recordId, 'vtiger_crmentity.setype' => 'Accounts'])->scalar();
	}

	protected static $datashareRelatedCache = false;

	/**
	 * Function to get data share related modules.
	 *
	 * @return array
	 */
	public static function getDatashareRelatedModules()
	{
		if (false === static::$datashareRelatedCache) {
			$relModSharArr = [];
			$dataReader = (new \App\Db\Query())->from('vtiger_datashare_relatedmodules')->createCommand()->query();
			while ($row = $dataReader->read()) {
				$relTabId = $row['relatedto_tabid'];
				if (isset($relModSharArr[$relTabId]) && \is_array($relModSharArr[$relTabId])) {
					$temArr = $relModSharArr[$relTabId];
					$temArr[] = $row['tabid'];
				} else {
					$temArr = [];
					$temArr[] = $row['tabid'];
				}
				$relModSharArr[$relTabId] = $temArr;
			}
			static::$datashareRelatedCache = $relModSharArr;
		}
		return static::$datashareRelatedCache;
	}

	protected static $defaultSharingActionCache = false;

	/**
	 * This Function returns the Default Organisation Sharing Action Array for all modules.
	 *
	 * @return array
	 */
	public static function getAllDefaultSharingAction()
	{
		if (false === static::$defaultSharingActionCache) {
			Log::trace('getAllDefaultSharingAction');
			static::$defaultSharingActionCache = array_map('intval', (new \App\Db\Query())->select(['tabid', 'permission'])->from('vtiger_def_org_share')->createCommand()->queryAllByGroup(0));
		}
		return static::$defaultSharingActionCache;
	}

	/**
	 * Function to get the vtiger_role related user ids.
	 *
	 * @param string $roleId Role ID
	 *
	 * @return array $users Role related user array
	 */
	public static function getUsersByRole($roleId): array
	{
		if (Cache::has('getUsersByRole', $roleId)) {
			return Cache::get('getUsersByRole', $roleId);
		}
		$users = static::getQueryToUsersByRole($roleId)->column();
		$users = array_map('intval', $users);
		Cache::save('getUsersByRole', $roleId, $users);
		return $users;
	}

	/**
	 * Function to get the users names by role.
	 *
	 * @param int $roleId
	 *
	 * @return array $users
	 */
	public static function getUsersNameByRole($roleId)
	{
		if (Cache::has('getUsersNameByRole', $roleId)) {
			return Cache::get('getUsersNameByRole', $roleId);
		}
		$users = static::getUsersByRole($roleId);
		$roleRelatedUsers = [];
		if ($users) {
			foreach ($users as $userId) {
				$roleRelatedUsers[$userId] = Fields\Owner::getUserLabel($userId);
			}
		}
		Cache::save('getUsersNameByRole', $roleId, $roleRelatedUsers);
		return $roleRelatedUsers;
	}

	/**
	 * Function to get the role related user ids.
	 *
	 * @param int $userId RoleId :: Type varchar
	 */
	public static function getRoleByUsers($userId)
	{
		if (Cache::has('getRoleByUsers', $userId)) {
			return Cache::get('getRoleByUsers', $userId);
		}
		$roleId = (new \App\Db\Query())->select(['roleid'])
			->from('vtiger_user2role')->where(['userid' => $userId])
			->scalar();
		Cache::save('getRoleByUsers', $userId, $roleId);
		return $roleId;
	}

	/**
	 * Function to get user groups.
	 *
	 * @param int $userId
	 *
	 * @return array - groupId's
	 */
	public static function getUserGroups($userId)
	{
		if (Cache::has('UserGroups', $userId)) {
			return Cache::get('UserGroups', $userId);
		}
		$groupIds = (new \App\Db\Query())->select(['groupid'])->from('vtiger_users2group')->where(['userid' => $userId])->column();
		$groupIds = array_map('intval', $groupIds);
		Cache::save('UserGroups', $userId, $groupIds);
		return $groupIds;
	}

	/**
	 * This function is to retreive the vtiger_profiles associated with the  the specified role.
	 *
	 * @param string $roleId
	 *
	 * @return array
	 */
	public static function getProfilesByRole($roleId)
	{
		$profiles = Cache::staticGet('getProfilesByRole', $roleId);
		if ($profiles) {
			return $profiles;
		}
		$profiles = (new \App\Db\Query())
			->select(['profileid'])
			->from('vtiger_role2profile')
			->where(['roleid' => $roleId])
			->column();
		$profiles = array_map('intval', $profiles);
		Cache::staticSave('getProfilesByRole', $roleId, $profiles);
		return $profiles;
	}

	/**
	 *  This function is to retreive the vtiger_profiles associated with the  the specified user.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public static function getProfilesByUser($userId)
	{
		$roleId = static::getRoleByUsers($userId);

		return static::getProfilesByRole($roleId);
	}

	const MEMBER_TYPE_USERS = 'Users';
	const MEMBER_TYPE_GROUPS = 'Groups';
	const MEMBER_TYPE_ROLES = 'Roles';
	const MEMBER_TYPE_ROLE_AND_SUBORDINATES = 'RoleAndSubordinates';

	protected static $membersCache = false;

	/**
	 * Function to get all members.
	 *
	 * @return array
	 */
	public static function getMembers()
	{
		if (false === static::$membersCache) {
			$members = [];
			$owner = new \App\Fields\Owner();
			foreach ($owner->initUsers() as $id => $user) {
				$members[static::MEMBER_TYPE_USERS][static::MEMBER_TYPE_USERS . ':' . $id] = ['name' => $user['fullName'], 'id' => $id, 'type' => static::MEMBER_TYPE_USERS];
			}
			foreach ($owner->getGroups(false) as $id => $groupName) {
				$members[static::MEMBER_TYPE_GROUPS][static::MEMBER_TYPE_GROUPS . ':' . $id] = ['name' => $groupName, 'id' => $id, 'type' => static::MEMBER_TYPE_GROUPS];
			}
			foreach (\Settings_Roles_Record_Model::getAll() as $id => $roleModel) {
				$members[static::MEMBER_TYPE_ROLES][static::MEMBER_TYPE_ROLES . ':' . $id] = ['name' => $roleModel->getName(), 'id' => $id, 'type' => static::MEMBER_TYPE_ROLES];
				$members[static::MEMBER_TYPE_ROLE_AND_SUBORDINATES][static::MEMBER_TYPE_ROLE_AND_SUBORDINATES . ':' . $id] = ['name' => $roleModel->getName(), 'id' => $id, 'type' => static::MEMBER_TYPE_ROLE_AND_SUBORDINATES];
			}
			static::$membersCache = $members;
		}
		return static::$membersCache;
	}

	/**
	 * Get list of users based on members, eg. Users:2, Roles:H2.
	 *
	 * @param string $member
	 *
	 * @return array
	 */
	public static function getUserByMember($member)
	{
		if (Cache::has('getUserByMember', $member)) {
			return Cache::get('getUserByMember', $member);
		}
		[$type, $id] = explode(':', $member);
		$users = [];
		switch ($type) {
			case 'Users':
				$users[] = (int) $id;
				break;
			case 'Groups':
				$users = array_merge($users, static::getUsersByGroup($id));
				break;
			case 'Roles':
				$users = array_merge($users, static::getUsersByRole($id));
				break;
			case 'RoleAndSubordinates':
				$users = array_merge($users, static::getUsersByRoleAndSubordinate($id));
				break;
			default:
				break;
		}
		$users = array_unique($users);
		Cache::save('getUserByMember', $member, $users, Cache::LONG);
		return $users;
	}

	/**
	 * Get list of users based on group id.
	 *
	 * @param int        $groupId
	 * @param array|bool $subGroups
	 * @param int        $i
	 *
	 * @return array
	 */
	public static function getUsersByGroup($groupId, $subGroups = false, $i = 0)
	{
		$cacheKey = $groupId . (false === $subGroups ? '' : '#');
		if (Cache::has('getUsersByGroup', $cacheKey)) {
			return Cache::get('getUsersByGroup', $cacheKey);
		}
		if (false === $subGroups) {
			$users = static::getQueryToUsersByGroup($groupId)->column();
		} else {
			$users = static::getQueryToUsersByGroup($groupId, false)->column();
			if ($i < self::GROUP_LOOP_LIMIT) {
				++$i;
				if (true === $subGroups) {
					$subGroups = [];
				}
				$dataReader = (new \App\Db\Query())->select(['containsgroupid'])->from('vtiger_group2grouprel')->where(['groupid' => $groupId])->createCommand()->query();
				$containsGroups = [];
				while ($containsGroupId = $dataReader->readColumn(0)) {
					$roleUsers = static::getUsersByGroup($containsGroupId, $subGroups, $i);
					$containsGroups = array_merge($containsGroups, $roleUsers['users']);
					if (!isset($subGroups[$containsGroupId])) {
						$subGroups[$containsGroupId] = $containsGroups;
					}
					foreach ($roleUsers['subGroups'] as $key => $value) {
						if (!isset($subGroups[$key])) {
							$subGroups[$key] = $containsGroups;
						}
					}
				}
				if ($containsGroups) {
					$users = array_merge($users, $containsGroups);
				}
			} else {
				Log::error('Exceeded the recursive limit, a loop might have been created. Group ID:' . $groupId);
			}
		}
		$users = array_unique($users);
		$return = (false === $subGroups ? $users : ['users' => $users, 'subGroups' => $subGroups]);
		Cache::save('getUsersByGroup', $cacheKey, $return, Cache::LONG);
		return $return;
	}

	/**
	 * Gets query to users by members.
	 *
	 * @param array $members
	 *
	 * @return Db\Query
	 */
	public static function getQueryToUsersByMembers(array $members): Db\Query
	{
		$queryGenerator = (new \App\QueryGenerator('Users'))->setFields(['id']);
		$columName = $queryGenerator->getColumnName('id');
		$conditions = ['or'];
		foreach ($members as $member) {
			[$type, $id] = explode(':', $member);
			switch ($type) {
					case self::MEMBER_TYPE_USERS:
						if (!isset($conditions[$type])) {
							$conditions[$type][$columName] = [(int) $id];
						} else {
							$conditions[$type][$columName][] = (int) $id;
						}
						break;
					case self::MEMBER_TYPE_GROUPS:
						$conditions[] = [$columName => (new \App\Db\Query())->select(['userid'])->from(["condition_{$type}_{$id}_" . \App\Layout::getUniqueId() => self::getQueryToUsersByGroup((int) $id)])];
						break;
					case self::MEMBER_TYPE_ROLES:
						$conditions[] = [$columName => self::getQueryToUsersByRole($id)];
						break;
					case self::MEMBER_TYPE_ROLE_AND_SUBORDINATES:
						$conditions[] = [$columName => self::getQueryToUsersByRoleAndSubordinate($id)];
						break;
					default:
						break;
				}
		}
		if (\count($conditions) <= 1) {
			$conditions[] = [$columName => -1];
		}

		return $queryGenerator->setFields(['id'])->addNativeCondition(array_values($conditions))->createQuery();
	}

	/**
	 * Gets query to users by group.
	 *
	 * @param int  $groupId
	 * @param bool $recursive
	 * @param int  $depth
	 *
	 * @return Db\Query
	 */
	public static function getQueryToUsersByGroup(int $groupId, bool $recursive = true, int $depth = 0): Db\Query
	{
		++$depth;
		$query = (new \App\Db\Query())->select(['userid'])->from('vtiger_users2group')->where(['groupid' => $groupId])
			->union(
			(new \App\Db\Query())->select(['vtiger_user2role.userid'])
				->from('vtiger_group2role')
				->innerJoin('vtiger_user2role', 'vtiger_group2role.roleid=vtiger_user2role.roleid')->where(['groupid' => $groupId])
			)
			->union(
			(new \App\Db\Query())->select(['vtiger_user2role.userid'])->from('vtiger_group2rs')
				->innerJoin('vtiger_role', "vtiger_group2rs.roleandsubid=vtiger_role.roleid OR vtiger_role.parentrole like CONCAT('%', vtiger_group2rs.roleandsubid, '::%')")
				->innerJoin('vtiger_user2role', 'vtiger_role.roleid=vtiger_user2role.roleid')
				->where(['vtiger_group2rs.groupid' => $groupId])
			);
		if ($recursive) {
			if ($depth < self::GROUP_LOOP_LIMIT) {
				$dataReader = (new \App\Db\Query())->select(['containsgroupid'])->from('vtiger_group2grouprel')->where(['groupid' => $groupId])->createCommand()->query();
				while ($containsGroupId = $dataReader->readColumn(0)) {
					$query->union((new \App\Db\Query())->select(['userid'])->from(["query_{$groupId}_{$containsGroupId}_{$depth}" => static::getQueryToUsersByGroup($containsGroupId, $recursive, $depth)]));
				}
				$dataReader->close();
			} else {
				Log::error('Exceeded the recursive limit, a loop might have been created. Group ID:' . $groupId);
			}
		}
		return $query;
	}

	/**
	 * Gets query to users by role.
	 *
	 * @param string $roleId
	 *
	 * @return Db\Query
	 */
	public static function getQueryToUsersByRole(string $roleId): Db\Query
	{
		return (new \App\Db\Query())->select(['userid'])->from('vtiger_user2role')->where(['roleid' => $roleId]);
	}

	/**
	 * Gets query to users by role and subordinate.
	 *
	 * @param string $roleId
	 *
	 * @return Db\Query
	 */
	public static function getQueryToUsersByRoleAndSubordinate(string $roleId): Db\Query
	{
		$parentRole = static::getRoleDetail($roleId)['parentrole'] ?? '-';
		return (new \App\Db\Query())->select(['vtiger_user2role.userid'])->from('vtiger_user2role')
			->innerJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid')
			->where(['or', ['vtiger_role.parentrole' => $parentRole], ['like', 'vtiger_role.parentrole', "{$parentRole}::%", false]]);
	}

	/**
	 * Function to get the roles and subordinate users.
	 *
	 * @param string $roleId
	 *
	 * @return array
	 */
	public static function getUsersByRoleAndSubordinate($roleId)
	{
		if (Cache::has('getUsersByRoleAndSubordinate', $roleId)) {
			return Cache::get('getUsersByRoleAndSubordinate', $roleId);
		}
		$users = static::getQueryToUsersByRoleAndSubordinate($roleId)->column();
		$users = array_map('intval', $users);
		Cache::save('getUsersByRoleAndSubordinate', $roleId, $users, Cache::LONG);

		return $users;
	}

	/**
	 * Function to get the vtiger_role information of the specified vtiger_role.
	 *
	 * @param $roleId
	 *
	 * @return array|bool|string
	 */
	public static function getRoleDetail($roleId)
	{
		if (Cache::has('RoleDetail', $roleId)) {
			return Cache::get('RoleDetail', $roleId);
		}
		$row = (new Db\Query())->from('vtiger_role')->where(['roleid' => $roleId])->one();
		if ($row) {
			$parentRoleArr = explode('::', $row['parentrole']);
			array_pop($parentRoleArr);
			$row['parentRoles'] = $parentRoleArr;
			$immediateParent = array_pop($parentRoleArr);
			$row['immediateParent'] = $immediateParent;
		}
		Cache::save('RoleDetail', $roleId, $row);
		return $row;
	}

	/**
	 * Function to get the role name.
	 *
	 * @param int $roleId
	 *
	 * @return string
	 */
	public static function getRoleName($roleId)
	{
		$roleInfo = static::getRoleDetail($roleId);
		return $roleInfo['rolename'];
	}

	/**
	 * To retreive the parent vtiger_role of the specified vtiger_role.
	 *
	 * @param $roleid -- The Role Id:: Type varchar
	 * @param mixed $roleId
	 *
	 * @return parent vtiger_role array in the following format:
	 */
	public static function getParentRole($roleId)
	{
		$roleInfo = static::getRoleDetail($roleId);
		return $roleInfo['parentRoles'];
	}

	/**
	 * To retreive the subordinate vtiger_roles of the specified parent vtiger_role.
	 *
	 * @param int $roleId
	 *
	 * @return array
	 */
	public static function getRoleSubordinates($roleId)
	{
		if (Cache::has('getRoleSubordinates', $roleId)) {
			return Cache::get('getRoleSubordinates', $roleId);
		}
		$roleDetails = static::getRoleDetail($roleId);
		$roleSubordinates = (new \App\Db\Query())
			->select(['roleid'])
			->from('vtiger_role')
			->where(['like', 'parentrole', $roleDetails['parentrole'] . '::%', false])
			->column();
		Cache::save('getRoleSubordinates', $roleId, $roleSubordinates, Cache::LONG);
		return $roleSubordinates;
	}

	/**
	 * Function to get the Profile Tab Permissions for the specified vtiger_profileid.
	 *
	 * @param int $profileid
	 *
	 * @return int[]
	 */
	public static function getProfileTabsPermission($profileid)
	{
		Log::trace('Entering getProfileTabsPermission(' . $profileid . ') method ...');
		if (Cache::has('getProfileTabsPermission', $profileid)) {
			return Cache::get('getProfileTabsPermission', $profileid);
		}
		$profileData = (new Db\Query())->select(['tabid', 'permissions'])->from('vtiger_profile2tab')->where(['profileid' => $profileid])->createCommand()->queryAllByGroup(0);
		$profileData = array_map('intval', $profileData);
		Cache::save('getProfileTabsPermission', $profileid, $profileData);
		Log::trace('Exiting getProfileTabsPermission method ...');
		return $profileData;
	}

	/**
	 * Function to get the Profile Global Information for the specified vtiger_profileid.
	 *
	 * @param int $profileid
	 *
	 * @return int[]
	 */
	public static function getProfileGlobalPermission($profileid)
	{
		if (Cache::has('getProfileGlobalPermission', $profileid)) {
			return Cache::get('getProfileGlobalPermission', $profileid);
		}
		$profileData = (new Db\Query())->select(['globalactionid', 'globalactionpermission'])->from('vtiger_profile2globalpermissions')
			->where(['profileid' => $profileid])->createCommand()->queryAllByGroup(0);
		$profileData = array_map('intval', $profileData);
		Cache::save('getProfileGlobalPermission', $profileid, $profileData);
		return $profileData;
	}

	/**
	 * To retreive the global permission of the specifed user from the various vtiger_profiles associated with the user.
	 *
	 * @param int $userId
	 *
	 * @return int[]
	 */
	public static function getCombinedUserGlobalPermissions($userId)
	{
		if (Cache::staticHas('getCombinedUserGlobalPermissions', $userId)) {
			return Cache::staticGet('getCombinedUserGlobalPermissions', $userId);
		}
		$userGlobalPerrArr = [];
		$profArr = static::getProfilesByUser($userId);
		$profileId = array_shift($profArr);
		if ($profileId) {
			$userGlobalPerrArr = static::getProfileGlobalPermission($profileId);
			foreach ($profArr as $profileId) {
				$tempUserGlobalPerrArr = static::getProfileGlobalPermission($profileId);
				foreach ($userGlobalPerrArr as $globalActionId => $globalActionPermission) {
					if (1 === $globalActionPermission) {
						$permission = $tempUserGlobalPerrArr[$globalActionId];
						if (0 === $permission) {
							$userGlobalPerrArr[$globalActionId] = $permission;
						}
					}
				}
			}
		}
		Cache::staticSave('getCombinedUserGlobalPermissions', $userId, $userGlobalPerrArr);
		return $userGlobalPerrArr;
	}

	/**
	 * To retreive the vtiger_tab permissions of the specifed user from the various vtiger_profiles associated with the user.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public static function getCombinedUserModulesPermissions($userId)
	{
		if (Cache::staticHas('getCombinedUserModulesPermissions', $userId)) {
			return Cache::staticGet('getCombinedUserModulesPermissions', $userId);
		}
		$profArr = static::getProfilesByUser($userId);
		$profileId = array_shift($profArr);
		if ($profileId) {
			$userTabPerrArr = static::getProfileTabsPermission($profileId);
			foreach ($profArr as $profileId) {
				$tempUserTabPerrArr = static::getProfileTabsPermission($profileId);
				foreach ($userTabPerrArr as $tabId => $tabPermission) {
					if (1 === $tabPermission) {
						$permission = $tempUserTabPerrArr[$tabId];
						if (0 === $permission) {
							$userTabPerrArr[$tabId] = $permission;
						}
					}
				}
			}
		}
		$homeId = Module::getModuleId('Home');
		if (!isset($userTabPerrArr[$homeId])) {
			$dashBoardId = Module::getModuleId('Dashboard');
			$userTabPerrArr[$homeId] = $userTabPerrArr[$dashBoardId] ?? 1;
		}
		Cache::staticSave('getCombinedUserModulesPermissions', $userId, $userTabPerrArr);
		return $userTabPerrArr;
	}

	/**
	 * Function to get all the vtiger_tab utility action permission for the specified vtiger_profile.
	 *
	 * @param int $profileid
	 *
	 * @return array
	 */
	public static function getUtilityPermissions($profileid)
	{
		$permissions = [];
		$dataReader = (new Db\Query())->from('vtiger_profile2utility')
			->where(['profileid' => $profileid])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$permissions[$row['tabid']][$row['activityid']] = (int) $row['permission'];
		}
		return $permissions;
	}

	/**
	 * Function to get the Profile Action Permissions for the specified vtiger_profileid.
	 *
	 * @param int $profileid
	 *
	 * @return array
	 */
	public static function getStandardPermissions($profileid)
	{
		$permissions = [];
		$dataReader = (new Db\Query())->from('vtiger_profile2standardpermissions')
			->where(['profileid' => $profileid])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$permissions[$row['tabid']][$row['operation']] = (int) $row['permissions'];
		}
		return $permissions;
	}

	/**
	 * Function to get the Standard and Utility Profile Action Permissions for the specified vtiger_profileid.
	 *
	 * @param int $profileid
	 *
	 * @return array
	 */
	public static function getAllProfilePermissions($profileid)
	{
		if (Cache::staticHas(__METHOD__, $profileid)) {
			return Cache::staticGet(__METHOD__, $profileid);
		}
		$allActions = static::getStandardPermissions($profileid);
		$utilityActions = static::getUtilityPermissions($profileid);
		foreach ($utilityActions as $tabid => $utilityAction) {
			$actionTabs = $allActions[$tabid] ?? [];
			foreach ($utilityAction as $utilityId => $utilityPermission) {
				$actionTabs[$utilityId] = (int) $utilityPermission;
			}
			$allActions[$tabid] = $actionTabs;
		}
		Cache::staticSave(__METHOD__, $profileid, $allActions);
		return $allActions;
	}

	/**
	 * To retreive the vtiger_tab acion permissions of the specifed user from the various vtiger_profiles associated with the user.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public static function getCombinedUserActionsPermissions($userId)
	{
		$profiles = static::getProfilesByUser($userId);
		$actionPermissions = [];
		if (isset($profiles[0])) {
			$actionPermissions = static::getAllProfilePermissions($profiles[0]);
			unset($profiles[0]);
		}
		if (\is_array($profiles)) {
			foreach ($profiles as $profileId) {
				$tempActionPerrArr = static::getAllProfilePermissions($profileId);
				foreach ($actionPermissions as $tabId => $permissionsInModule) {
					foreach ($permissionsInModule as $actionId => $permission) {
						if (1 == $permission) {
							$nowPermission = $tempActionPerrArr[$tabId][$actionId];
							if (0 == $nowPermission && '' != $nowPermission) {
								$actionPermissions[$tabId][$actionId] = $nowPermission;
							}
						}
					}
				}
			}
		}
		return $actionPermissions;
	}

	protected static $dataShareStructure = [
		'role2role' => ['vtiger_datashare_role2role', 'to_roleid'],
		'role2rs' => ['vtiger_datashare_role2rs', 'to_roleandsubid'],
		'role2group' => ['vtiger_datashare_role2group', 'to_groupid'],
		'role2user' => ['vtiger_datashare_role2us', 'to_userid'],
		'rs2role' => ['vtiger_datashare_rs2role', 'to_roleid'],
		'rs2rs' => ['vtiger_datashare_rs2rs', 'to_roleandsubid'],
		'rs2group' => ['vtiger_datashare_rs2grp', 'to_groupid'],
		'rs2user' => ['vtiger_datashare_rs2us', 'to_userid'],
		'group2role' => ['vtiger_datashare_grp2role', 'to_roleid'],
		'group2rs' => ['vtiger_datashare_grp2rs', 'to_roleandsubid'],
		'group2user' => ['vtiger_datashare_grp2us', 'to_userid'],
		'group2group' => ['vtiger_datashare_grp2grp', 'to_groupid'],
		'user2user' => ['vtiger_datashare_us2us', 'to_userid'],
		'user2group' => ['vtiger_datashare_us2grp', 'to_groupid'],
		'user2role' => ['vtiger_datashare_us2role', 'to_roleid'],
		'user2rs' => ['vtiger_datashare_us2rs', 'to_roleandsubid'],
	];

	/**
	 * Get data share.
	 *
	 * @param int   $tabId
	 * @param int   $roleId
	 * @param mixed $type
	 * @param mixed $data
	 *
	 * @return array
	 */
	public static function getDatashare($type, $tabId, $data)
	{
		$cacheKey = "$type|$tabId|" . (\is_array($data) ? implode(',', $data) : $data);
		if (Cache::staticHas('getDatashare', $cacheKey)) {
			return Cache::staticGet('getDatashare', $cacheKey);
		}
		$structure = static::$dataShareStructure[$type];
		$query = (new \App\Db\Query())->select([$structure[0] . '.*'])->from($structure[0])
			->innerJoin('vtiger_datashare_module_rel', "$structure[0].shareid = vtiger_datashare_module_rel.shareid")
			->where(['vtiger_datashare_module_rel.tabid' => $tabId]);
		if ($data) {
			$query->andWhere([$structure[1] => $data]);
		}
		$rows = $query->all();
		Cache::staticSave('getDatashare', $cacheKey, $rows);
		return $rows;
	}

	/**
	 * Gives an array which contains the information for what all roles, groups and user data is to be shared with the spcified user for the specified module.
	 *
	 * @param string $module            module name
	 * @param int    $userid            user id
	 * @param array  $defOrgShare       default organization sharing permission array
	 * @param string $currentUserRoles  roleid
	 * @param string $parentRoles       parent roles
	 * @param int    $currentUserGroups user id
	 *
	 * @return array array which contains the id of roles,group and users data shared with specifed user for the specified module
	 */
	public static function getUserModuleSharingObjects($module, $userid, $defOrgShare, $currentUserRoles, $parentRoles, $currentUserGroups)
	{
		$modTabId = Module::getModuleId($module);
		$modShareWritePermission = $modShareReadPermission = ['ROLE' => [], 'GROUP' => []];
		$modDefOrgShare = null;
		if (isset($defOrgShare[$modTabId])) {
			$modDefOrgShare = $defOrgShare[$modTabId];
		}
		$shareIdMembers = [];
		//If Sharing of leads is Private
		if (3 === $modDefOrgShare || 0 === $modDefOrgShare) {
			$roleWritePer = $roleWritePer = $grpReadPer = $grpWritePer = $roleReadPer = [];
			//Retreiving from vtiger_role to vtiger_role
			foreach (static::getDatashare('role2role', $modTabId, $currentUserRoles) as $row) {
				$shareRoleId = $row['share_roleid'];
				$shareIdRoles = [];
				$shareIdRoles[] = $shareRoleId;
				$shareIdMembers[$row['shareid']] = ['ROLE' => $shareIdRoles];
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($roleReadPer[$shareRoleId])) {
						$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
					}
					if (!isset($roleWritePer[$shareRoleId])) {
						$roleWritePer[$shareRoleId] = static::getUsersByRole($shareRoleId);
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($roleReadPer[$shareRoleId])) {
						$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
					}
				}
			}
			//Retreiving from role to rs
			$parRoleList = [];
			if (\is_array($parentRoles)) {
				foreach ($parentRoles as $par_role_id) {
					$parRoleList[] = $par_role_id;
				}
			}
			$parRoleList[] = $currentUserRoles;
			foreach (static::getDatashare('role2rs', $modTabId, $parRoleList) as $row) {
				$shareRoleId = $row['share_roleid'];
				$shareIdRoles = [];
				$shareIdRoles[] = $shareRoleId;
				$shareIdMembers[$row['shareid']] = ['ROLE' => $shareIdRoles];
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($roleReadPer[$shareRoleId])) {
						$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
					}
					if (!isset($roleWritePer[$shareRoleId])) {
						$roleWritePer[$shareRoleId] = static::getUsersByRole($shareRoleId);
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($roleReadPer[$shareRoleId])) {
						$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
					}
				}
			}
			//Get roles from Role2Grp
			$groupList = $currentUserGroups;
			if (empty($groupList)) {
				$groupList = [0];
			}
			if ($groupList) {
				foreach (static::getDatashare('role2group', $modTabId, $groupList) as $row) {
					$shareRoleId = $row['share_roleid'];
					$shareIdRoles = [];
					$shareIdRoles[] = $shareRoleId;
					$shareIdMembers[$row['shareid']] = ['ROLE' => $shareIdRoles];
					if (1 === (int) $row['permission']) {
						if (3 === $modDefOrgShare && !isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
						if (!isset($roleWritePer[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					}
				}
			}
			//Get roles from Role2Us
			if (!empty($userid)) {
				foreach (static::getDatashare('role2user', $modTabId, $userid) as $row) {
					$shareRoleId = $row['share_roleid'];
					$shareIdRoles = [];
					$shareIdRoles[] = $shareRoleId;
					$shareIdMembers[$row['shareid']] = ['ROLE' => $shareIdRoles];
					if (1 === (int) $row['permission']) {
						if (3 === $modDefOrgShare && !isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
						if (!isset($roleWritePer[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare && !isset($roleReadPer[$shareRoleId])) {
						$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
					}
				}
			}
			//Retreiving from rs to vtiger_role
			foreach (static::getDatashare('rs2role', $modTabId, $currentUserRoles) as $row) {
				$shareRoleIds = static::getRoleSubordinates($row['share_roleandsubid']);
				$shareRoleIds[] = $row['share_roleandsubid'];
				foreach ($shareRoleIds as $shareRoleId) {
					if (1 === (int) $row['permission']) {
						if (3 === $modDefOrgShare && !isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
						if (!isset($roleWritePer[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					}
				}
				$shareIdMembers[$row['shareid']] = ['ROLE' => $shareRoleIds];
			}
			//Retreiving from rs to rs
			foreach (static::getDatashare('rs2rs', $modTabId, $parRoleList) as $row) {
				$shareRoleIds = static::getRoleSubordinates($row['share_roleandsubid']);
				$shareRoleIds[] = $row['share_roleandsubid'];
				foreach ($shareRoleIds as $shareRoleId) {
					if (1 === (int) $row['permission']) {
						if (3 === $modDefOrgShare && !isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
						if (!isset($roleWritePer[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					}
				}
				$shareIdMembers[$row['shareid']] = ['ROLE' => $shareRoleIds];
			}
			//Get roles from Rs2Grp
			foreach (static::getDatashare('rs2group', $modTabId, $groupList) as $row) {
				$shareRoleIds = static::getRoleSubordinates($row['share_roleandsubid']);
				$shareRoleIds[] = $row['share_roleandsubid'];
				foreach ($shareRoleIds as $shareRoleId) {
					if (1 === (int) $row['permission']) {
						if (3 === $modDefOrgShare && !isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
						if (!isset($roleWritePer[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					}
				}
				$shareIdMembers[$row['shareid']] = ['ROLE' => $shareRoleIds];
			}
			//Get roles from Rs2Us
			foreach (static::getDatashare('rs2user', $modTabId, $userid) as $row) {
				$shareRoleIds = static::getRoleSubordinates($row['share_roleandsubid']);
				$shareRoleIds[] = $row['share_roleandsubid'];
				foreach ($shareRoleIds as $shareRoleId) {
					if (1 === (int) $row['permission']) {
						if (3 === $modDefOrgShare && !isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
						if (!isset($roleWritePer[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = static::getUsersByRole($shareRoleId);
						}
					}
				}
				$shareIdMembers[$row['shareid']] = ['ROLE' => $shareRoleIds];
			}
			$modShareReadPermission['ROLE'] = $roleReadPer;
			$modShareWritePermission['ROLE'] = $roleWritePer;

			//Retreiving from the grp2role sharing
			foreach (static::getDatashare('group2role', $modTabId, $currentUserRoles) as $row) {
				$shareGrpId = (int) $row['share_groupid'];
				$shareIdGrps = [];
				$shareIdGrps[] = $shareGrpId;
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($grpReadPer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpReadPer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
					if (!isset($grpWritePer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpWritePer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpWritePer[$subgrpid])) {
								$grpWritePer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($grpReadPer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpReadPer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				}
				$shareIdMembers[$row['shareid']] = ['GROUP' => $shareIdGrps];
			}
			//Retreiving from the grp2rs sharing
			foreach (static::getDatashare('group2rs', $modTabId, $parRoleList) as $row) {
				$shareGrpId = (int) $row['share_groupid'];
				$shareIdGrps = [];
				$shareIdGrps[] = $shareGrpId;
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($grpReadPer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpReadPer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
					if (!isset($grpWritePer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpWritePer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpWritePer[$subgrpid])) {
								$grpWritePer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($grpReadPer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpReadPer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				}
				$shareIdMembers[$row['shareid']] = ['GROUP' => $shareIdGrps];
			}
			//Retreiving from the grp2us sharing
			foreach (static::getDatashare('group2user', $modTabId, $userid) as $row) {
				$shareGrpId = (int) $row['share_groupid'];
				$shareIdGrps = [];
				$shareIdGrps[] = $shareGrpId;
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($grpReadPer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpReadPer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
					if (!isset($grpWritePer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpWritePer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpWritePer[$subgrpid])) {
								$grpWritePer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($grpReadPer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpReadPer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				}
				$shareIdMembers[$row['shareid']] = ['GROUP' => $shareIdGrps];
			}
			//Retreiving from the grp2grp sharing
			foreach (static::getDatashare('group2group', $modTabId, $groupList) as $row) {
				$shareGrpId = (int) $row['share_groupid'];
				$shareIdGrps = [];
				$shareIdGrps[] = $shareGrpId;
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($grpReadPer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpReadPer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
					if (!isset($grpWritePer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpWritePer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpWritePer[$subgrpid])) {
								$grpWritePer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($grpReadPer[$shareGrpId])) {
						$usersByGroup = static::getUsersByGroup($shareGrpId, true);
						$grpReadPer[$shareGrpId] = $usersByGroup['users'];
						foreach ($usersByGroup['subGroups'] as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!\in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				}
				$shareIdMembers[$row['shareid']] = ['GROUP' => $shareIdGrps];
			}
			//Get roles from Us2Us
			foreach (static::getDatashare('user2user', $modTabId, $userid) as $row) {
				$shareUserId = (int) $row['share_userid'];
				$shareIdUsers = [];
				$shareIdUsers[] = $shareUserId;
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
					if (!isset($grpWritePer[$shareUserId])) {
						$grpWritePer[$shareUserId] = [$shareUserId];
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
				}
				$shareIdMembers[$row['shareid']] = ['GROUP' => $shareIdUsers];
			}
			//Get roles from Us2Grp
			foreach (static::getDatashare('user2group', $modTabId, $groupList) as $row) {
				$shareUserId = (int) $row['share_userid'];
				$shareIdUsers = [];
				$shareIdUsers[] = $shareUserId;
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
					if (!isset($grpWritePer[$shareUserId])) {
						$grpWritePer[$shareUserId] = [$shareUserId];
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
				}
				$shareIdMembers[$row['shareid']] = ['GROUP' => $shareIdUsers];
			}
			//Get roles from Us2role
			foreach (static::getDatashare('user2role', $modTabId, $currentUserRoles) as $row) {
				$shareUserId = (int) $row['share_userid'];
				$shareIdUsers = [];
				$shareIdUsers[] = $shareUserId;
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
					if (!isset($grpWritePer[$shareUserId])) {
						$grpWritePer[$shareUserId] = [$shareUserId];
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
				}

				$shareIdMembers[$row['shareid']] = ['GROUP' => $shareIdUsers];
			}
			//Get roles from Us2rs
			foreach (static::getDatashare('user2rs', $modTabId, $parRoleList) as $row) {
				$shareUserId = (int) $row['share_userid'];
				$shareIdUsers = [];
				$shareIdUsers[] = $shareUserId;
				if (1 === (int) $row['permission']) {
					if (3 === $modDefOrgShare && !isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
					if (!isset($grpWritePer[$shareUserId])) {
						$grpWritePer[$shareUserId] = [$shareUserId];
					}
				} elseif (0 === (int) $row['permission'] && 3 === $modDefOrgShare) {
					if (!isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
				}
				$shareIdMembers[$row['shareid']] = ['GROUP' => $shareIdUsers];
			}
			$modShareReadPermission['GROUP'] = $grpReadPer;
			$modShareWritePermission['GROUP'] = $grpWritePer;
		}
		return [
			'read' => $modShareReadPermission,
			'write' => $modShareWritePermission,
			'sharingrules' => $shareIdMembers,
		];
	}

	/**
	 * Get all groups by user id.
	 *
	 * @param int $userId
	 *
	 * @return int[]
	 */
	public static function getAllGroupsByUser($userId)
	{
		if (Cache::has('getAllGroupsByUser', $userId)) {
			return Cache::get('getAllGroupsByUser', $userId);
		}
		$userGroups = static::getUserGroups($userId);
		$userRole = static::getRoleByUsers($userId);
		$roleGroups = (new \App\Db\Query())->select(['groupid'])->from('vtiger_group2role')->where(['roleid' => $userRole])->column();
		$roles = static::getParentRole($userRole);
		$roles[] = $userRole;
		$rsGroups = (new \App\Db\Query())->select(['groupid'])->from('vtiger_group2rs')->where(['roleandsubid' => $roles])->column();
		$allGroups = array_unique(array_merge($userGroups, $roleGroups, $rsGroups));
		$parentGroups = [];
		foreach ($allGroups as $groupId) {
			$parentGroups = array_merge($parentGroups, static::getParentGroups($groupId));
		}
		if ($parentGroups) {
			$allGroups = array_unique(array_merge($allGroups, $parentGroups));
		}
		Cache::save('getAllGroupsByUser', $userId, $allGroups, Cache::LONG);

		return $allGroups;
	}

	/**
	 * Get parent grioups by group id.
	 *
	 * @param int $groupId
	 * @param int $i
	 *
	 * @return int[]
	 */
	public static function getParentGroups($groupId, $i = 0)
	{
		$groups = [];
		if ($i < 10) {
			$dataReader = (new \App\Db\Query())->select(['groupid'])->from('vtiger_group2grouprel')->where(['containsgroupid' => $groupId])->createCommand()->query();
			while ($parentGroupId = $dataReader->readColumn(0)) {
				$groups = array_merge($groups, [$parentGroupId], static::getParentGroups($parentGroupId, $i++));
			}
		} else {
			Log::warning('Exceeded the recursive limit, a loop might have been created. Group ID:' . $groupId);
		}
		return $groups;
	}

	/**
	 * Creates a query to all groups where the user is a member..
	 *
	 * @param int $userId
	 *
	 * @return Db\Query
	 */
	public static function getQueryToGroupsByUserId(int $userId): Db\Query
	{
		return (new \App\Db\Query())->select(['groupid'])->from('vtiger_groups')->where(['groupid' => self::getAllGroupsByUser($userId)]);
	}

	/**
	 * Returns the leaders of the groups where the user is a member.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public static function getLeadersGroupByUserId(int $userId): array
	{
		$db = \App\Db::getInstance();
		$query = self::getQueryToGroupsByUserId($userId)->andWhere(['<>', 'parentid', 0])->andWhere(['not', ['parentid' => null]]);
		$member = new \yii\db\Expression('CASE WHEN vtiger_users.id IS NOT NULL THEN CONCAT(' . $db->quoteValue(self::MEMBER_TYPE_USERS) . ',\':\', parentid) ELSE CONCAT(' . $db->quoteValue(self::MEMBER_TYPE_GROUPS) . ',\':\', parentid) END');
		$query->select(['groupid', 'member' => $member])->leftJoin('vtiger_users', 'vtiger_groups.parentid=vtiger_users.id');

		return $query->createCommand()->queryAllByGroup(0);
	}

	/**
	 * Returns groups whose leader is the user.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public static function getGroupsWhereUserIsLeader(int $userId): array
	{
		return (new \App\Db\Query())->select(['groupid'])->from('vtiger_groups')->where(['or', ['parentid' => $userId], ['parentid' => self::getQueryToGroupsByUserId($userId)]])->column();
	}

	/**
	 * Tables to sharing rules.
	 *
	 * @var array
	 */
	private static $shareRulesTables = [
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
		'RS::US' => 'vtiger_datashare_rs2us',
	];

	/**
	 * List tables where sharing rules are save for users, groups and roles.
	 *
	 * @var array
	 */
	private static $shareRulesTablesIndex = [
		'Users' => [
			'vtiger_datashare_us2us' => 'share_userid::to_userid',
			'vtiger_datashare_us2grp' => 'share_userid',
			'vtiger_datashare_us2role' => 'share_userid',
			'vtiger_datashare_us2rs' => 'share_userid',
			'vtiger_datashare_grp2us' => 'to_userid',
			'vtiger_datashare_rs2us' => 'to_userid',
			'vtiger_datashare_role2us' => 'to_userid',
		],
		'Roles' => [
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
			'vtiger_datashare_rs2rs' => 'share_roleandsubid::to_roleandsubid',
		],
		'Groups' => [
			'vtiger_datashare_grp2grp' => 'share_groupid::to_groupid',
			'vtiger_datashare_grp2role' => 'share_groupid',
			'vtiger_datashare_grp2rs' => 'share_groupid',
			'vtiger_datashare_grp2us' => 'share_groupid',
			'vtiger_datashare_role2group' => 'to_groupid',
			'vtiger_datashare_rs2grp' => 'to_groupid',
			'vtiger_datashare_us2grp' => 'to_groupid',
		],
	];

	/**
	 * This function is to delete the organisation level sharing rule
	 * It takes the following input parameters:.
	 *
	 * @param int $shareid Id of the Sharing Rule to be updated
	 */
	private static function deleteSharingRule($shareid)
	{
		Log::trace('Entering deleteSharingRule(' . $shareid . ') method ...');
		$dbCommand = Db::getInstance()->createCommand();
		$typestr = (new Db\Query())->select(['relationtype'])->from('vtiger_datashare_module_rel')->where(['shareid' => $shareid])->scalar();
		$dbCommand->delete(static::$shareRulesTables[$typestr], ['shareid' => $shareid])->execute();
		$dbCommand->delete('vtiger_datashare_module_rel', ['shareid' => $shareid])->execute();
		$dbCommand->delete('vtiger_datashare_relatedmodule_permission', ['shareid' => $shareid])->execute();
		Log::trace('Exiting deleteSharingRule method ...');
	}

	/**
	 * Function to remove sharing rules from tables.
	 *
	 * @param int|string $id
	 * @param string     $type
	 */
	public static function deleteRelatedSharingRules($id, $type)
	{
		Log::trace('Entering deleteRelatedSharingRules(' . $id . ') method ...');
		foreach (static::$shareRulesTablesIndex[$type] as $tablename => $colname) {
			$colNameArr = explode('::', $colname);
			$query = (new Db\Query())->select(['shareid'])
				->from($tablename)
				->where([$colNameArr[0] => $id]);
			if (isset($colNameArr[1])) {
				$query->orWhere([$colNameArr[1] => $id]);
			}
			$dataReader = $query->createCommand()->query();
			while ($shareid = $dataReader->readColumn(0)) {
				static::deleteSharingRule($shareid);
			}
			$dataReader->close();
		}
		Log::trace('Exiting deleteRelatedSharingRules method ...');
	}

	/**
	 * Function for test to check privilege utils.
	 *
	 * @param int $recordId
	 */
	public static function testPrivileges($recordId)
	{
		static::getHelpDeskRelatedAccounts($recordId);
		return true;
	}

	/**
	 * Recalculate sharing rules by user id.
	 *
	 * @param int $id
	 */
	public static function recalculateSharingRulesByUser($id)
	{
		$userModel = \App\User::getUserModel($id);
		if (!$userModel->getId()) {
			return null;
		}
		$roles = explode('::', $userModel->getParentRolesSeq());
		$groups = $userModel->getGroups();
		$sharing = [];
		foreach (\Settings_SharingAccess_Rule_Model::$dataShareTableColArr['ROLE'] as $key => $item) {
			$row = (new \App\Db\Query())->select([$item['target_id']])->from($item['table'])->where([$item['source_id'] => $roles])->column();
			if ($row) {
				if (!isset($sharing[$key])) {
					$sharing[$key] = [];
				}
				$sharing[$key] = array_merge($sharing[$key], $row);
			}
		}
		foreach (\Settings_SharingAccess_Rule_Model::$dataShareTableColArr['RS'] as $key => $item) {
			$row = (new \App\Db\Query())->select([$item['target_id']])->from($item['table'])->where([$item['source_id'] => $roles])->column();
			if ($row) {
				if (!isset($sharing[$key])) {
					$sharing[$key] = [];
				}
				$sharing[$key] = array_merge($sharing[$key], $row);
			}
		}
		if ($groups) {
			foreach (\Settings_SharingAccess_Rule_Model::$dataShareTableColArr['GRP'] as $key => $item) {
				$row = (new \App\Db\Query())->select([$item['target_id']])->from($item['table'])->where([$item['source_id'] => $groups])->column();
				if ($row) {
					if (!isset($sharing[$key])) {
						$sharing[$key] = [];
					}
					$sharing[$key] = array_merge($sharing[$key], $row);
				}
			}
		}
		$users = [[]];
		foreach ($sharing as $type => $item) {
			switch ($type) {
				case 'US':
					$users[] = array_unique($item);
					break;
				case 'GRP':
					foreach ($item as $grpId) {
						$users[] = static::getUsersByGroup($grpId);
					}
					break;
				case 'ROLE':
					foreach ($item as $roleId) {
						$users[] = static::getUsersByRole($roleId);
					}
					break;
				case 'RS':
					foreach ($item as $roleId) {
						$users[] = static::getUsersByRoleAndSubordinate($roleId);
					}
					break;
				default:
					break;
			}
		}
		foreach (array_unique(array_merge(...$users)) as $userId) {
			UserPrivilegesFile::createUserSharingPrivilegesfile($userId);
		}
	}

	/**
	 * Modify permissions for actions and views.
	 *
	 * @param string $moduleName
	 * @param array  $actions
	 * @param bool   $mode       true: add, false: remove
	 *
	 * @return bool
	 */
	public static function modifyPermissions(string $moduleName, array $actions, bool $mode): bool
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$result = false;
		$tabId = Module::getModuleId($moduleName);
		$actions = array_diff($actions, array_merge(\Vtiger_Action_Model::$nonConfigurableActions, \Vtiger_Action_Model::$standardActions));
		$actionIds = array_filter(array_map('\App\Module::getActionId', $actions));
		if ($mode) {
			$profilesByAction = (new Db\Query())->select(['profileid', 'activityid'])
				->from('vtiger_profile2utility')
				->where(['tabid' => $tabId, 'activityid' => $actionIds])->createCommand()->queryAllByGroup(2);
			foreach (\vtlib\Profile::getAllIds() as $profileId) {
				$add = !isset($profilesByAction[$profileId]) ? $actionIds : array_diff($actionIds, $profilesByAction[$profileId]);
				foreach ($add as $actionId) {
					$result = $dbCommand->insert('vtiger_profile2utility', ['profileid' => $profileId, 'tabid' => $tabId, 'activityid' => $actionId, 'permission' => 1])->execute() || $result;
				}
			}
		} else {
			$result = $dbCommand->delete('vtiger_profile2utility', ['tabid' => $tabId, 'activityid' => $actionIds])->execute();
		}
		return $result;
	}
}
