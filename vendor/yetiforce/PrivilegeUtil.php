<?php
namespace App;

/**
 * Privilege Util basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PrivilegeUtil
{

	/** Function to get parent record owner
	 * @param $tabid -- tabid :: Type integer
	 * @param $parModId -- parent module id :: Type integer
	 * @param $recordId -- record id :: Type integer
	 * @returns $parentRecOwner -- parentRecOwner:: Type integer
	 */
	public static function getParentRecordOwner($tabid, $parModId, $recordId)
	{
		\App\Log::trace("Entering getParentRecordOwner($tabid,$parModId,$recordId) method ...");
		$parentRecOwner = [];
		$parentTabName = \vtlib\Functions::getModuleName($parModId);
		$relTabName = \vtlib\Functions::getModuleName($tabid);
		$fn_name = 'get' . $relTabName . 'Related' . $parentTabName;
		$entId = static::$fn_name($recordId);
		if ($entId != '') {
			$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($entId);
			if ($recordMetaData) {
				$ownerId = $recordMetaData['smownerid'];
				$type = \App\Fields\Owner::getType($ownerId);
				$parentRecOwner[$type] = $ownerId;
			}
		}
		\App\Log::trace('Exiting getParentRecordOwner method ...');
		return $parentRecOwner;
	}

	/** Function to get email related accounts
	 * @param $recordId -- record id :: Type integer
	 * @returns $accountid -- accountid:: Type integer
	 */
	private static function getEmailsRelatedAccounts($recordId)
	{
		\App\Log::trace("Entering getEmailsRelatedAccounts($recordId) method ...");
		$adb = \PearDatabase::getInstance();
		$query = "select vtiger_seactivityrel.crmid from vtiger_seactivityrel inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_seactivityrel.crmid where vtiger_crmentity.setype='Accounts' and activityid=?";
		$result = $adb->pquery($query, array($recordId));
		$accountid = $adb->getSingleValue($result);
		\App\Log::trace('Exiting getEmailsRelatedAccounts method ...');
		return $accountid;
	}

	/** Function to get email related Leads
	 * @param $recordId -- record id :: Type integer
	 * @returns $leadid -- leadid:: Type integer
	 */
	private static function getEmailsRelatedLeads($recordId)
	{
		\App\Log::trace("Entering getEmailsRelatedLeads($recordId) method ...");
		$adb = \PearDatabase::getInstance();
		$query = "select vtiger_seactivityrel.crmid from vtiger_seactivityrel inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_seactivityrel.crmid where vtiger_crmentity.setype='Leads' and activityid=?";
		$result = $adb->pquery($query, array($recordId));
		$leadid = $adb->getSingleValue($result);
		\App\Log::trace('Exiting getEmailsRelatedLeads method ...');
		return $leadid;
	}

	/** Function to get HelpDesk related Accounts
	 * @param $recordId -- record id :: Type integer
	 * @returns $accountid -- accountid:: Type integer
	 */
	private static function getHelpDeskRelatedAccounts($recordId)
	{
		\App\Log::trace("Entering getHelpDeskRelatedAccounts($recordId) method ...");
		$adb = \PearDatabase::getInstance();
		$query = "select parent_id from vtiger_troubletickets inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_troubletickets.parent_id where ticketid=? and vtiger_crmentity.setype='Accounts'";
		$result = $adb->pquery($query, array($recordId));
		$accountid = $adb->getSingleValue($result);
		\App\Log::trace('Exiting getHelpDeskRelatedAccounts method ...');
		return $accountid;
	}

	protected static $datashareRelatedCache = false;

	/**
	 * Function to get data share related modules
	 * @return array
	 */
	public static function getDatashareRelatedModules()
	{
		if (static::$datashareRelatedCache === false) {
			$relModSharArr = [];
			$adb = \PearDatabase::getInstance();
			$result = $adb->query('select * from vtiger_datashare_relatedmodules');
			while ($row = $adb->getRow($result)) {
				$relTabId = $row['relatedto_tabid'];
				if (is_array($relModSharArr[$relTabId])) {
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
	 * This Function returns the Default Organisation Sharing Action Array for all modules
	 * @return array
	 */
	public static function getAllDefaultSharingAction()
	{
		if (static::$defaultSharingActionCache === false) {
			\App\Log::trace('getAllDefaultSharingAction');
			$adb = \PearDatabase::getInstance();
			$copy = [];
			//retreiving the standard permissions
			$result = $adb->query('select * from vtiger_def_org_share');
			while ($row = $adb->getRow($result)) {
				$copy[$row['tabid']] = $row['permission'];
			}
			static::$defaultSharingActionCache = $copy;
		}
		return static::$defaultSharingActionCache;
	}

	protected static $usersByRoleCache = [];

	/**
	 * Function to get the vtiger_role related user ids
	 * @param int $roleId RoleId :: Type varchar
	 * @return array $users -- Role Related User Array in the following format:
	 */
	public static function getUsersByRole($roleId)
	{
		if (isset(static::$usersByRoleCache[$roleId])) {
			return static::$usersByRoleCache[$roleId];
		}
		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery('SELECT userid FROM vtiger_user2role WHERE roleid=?', array($roleId));
		$users = [];
		while (($userId = $adb->getSingleValue($result)) !== false) {
			$users[] = $userId;
		}
		static::$usersByRoleCache[$roleId] = $users;
		return $users;
	}

	protected static $roleByUsersCache = [];

	/**
	 * Function to get the role related user ids
	 * @param int $userId RoleId :: Type varchar
	 */
	public static function getRoleByUsers($userId)
	{
		if (isset(static::$roleByUsersCache[$userId])) {
			return static::$roleByUsersCache[$userId];
		}
		$roleId = (new \App\Db\Query())->select('roleid')
			->from('vtiger_user2role')->where(['userid' => $userId])
			->scalar();
		static::$roleByUsersCache[$userId] = $roleId;
		return $roleId;
	}

	/**
	 * Function to get user groups
	 * @param int $userId
	 * @return array - groupId's
	 */
	public static function getUserGroups($userId)
	{
		if (Cache::has('UserGroups', $userId)) {
			return Cache::get('UserGroups', $userId);
		}
		$groupIds = (new \App\Db\Query())->select('groupid')->from('vtiger_users2group')->where(['userid' => $userId])->column();
		Cache::save('UserGroups', $userId, $groupIds);
		return $groupIds;
	}

	/**
	 * Function to get role groups
	 * @param string $roleId
	 * @return array
	 */
	public static function getRoleGroups($roleId)
	{
		if (Cache::has('RoleGroups', $roleId)) {
			return Cache::get('RoleGroups', $roleId);
		}
		$groupIds = (new \App\Db\Query())->select('groupid')->from('vtiger_group2role')->where(['roleid' => $roleId])->column();
		Cache::save('RoleGroups', $roleId, $groupIds);
		return $groupIds;
	}

	/**
	 * Function to get role subordinates groups
	 * @param string $roleId
	 * @return array
	 */
	public static function getRoleSubordinatesGroups($roleId)
	{
		if (Cache::has('RoleSubordinatesGroups', $roleId)) {
			return Cache::get('RoleSubordinatesGroups', $roleId);
		}

		$roles = self::getParentRole($roleId);
		$roles [] = $roleId;
		$groupIds = (new \App\Db\Query())->select(['groupid'])->from('vtiger_group2rs')->where(['roleandsubid' => $roles])->column();
		Cache::save('RoleSubordinatesGroups', $roleId, $groupIds);
		return $groupIds;
	}

	/**
	 * This function is to retreive the vtiger_profiles associated with the  the specified role
	 * @param string $roleId
	 * @return array
	 */
	public static function getProfilesByRole($roleId)
	{
		$profiles = Cache::staticGet('getProfilesByRole', $roleId);
		if ($profiles) {
			return $profiles;
		}
		$profiles = (new \App\Db\Query())
			->select('profileid')
			->from('vtiger_role2profile')
			->where(['roleid' => $roleId])
			->column();
		Cache::staticSave('getProfilesByRole', $roleId, $profiles);
		return $profiles;
	}

	/**
	 *  This function is to retreive the vtiger_profiles associated with the  the specified user
	 * @param int $userId
	 * @return array
	 */
	public static function getProfilesByUser($userId)
	{
		$roleId = \App\PrivilegeUtil::getRoleByUsers($userId);
		return static::getProfilesByRole($roleId);
	}

	const MEMBER_TYPE_USERS = 'Users';
	const MEMBER_TYPE_GROUPS = 'Groups';
	const MEMBER_TYPE_ROLES = 'Roles';
	const MEMBER_TYPE_ROLE_AND_SUBORDINATES = 'RoleAndSubordinates';

	protected static $membersCache = false;

	/**
	 * Function to get all members
	 * @return array
	 */
	public static function getMembers()
	{
		if (static::$membersCache === false) {
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

	protected static $usersByMemberCache = [];

	/**
	 * Get list of users based on members, eg. Users:2, Roles:H2
	 * @param string $member
	 * @return array
	 */
	public static function getUserByMember($member)
	{
		if (isset(static::$usersByMemberCache[$member])) {
			return static::$usersByMemberCache[$member];
		}
		list($type, $id) = explode(':', $member);
		$users = [];
		switch ($type) {
			case 'Users' :
				$users[] = (int) $id;
				break;
			case 'Groups' :
				$users = array_merge($users, static::getUsersByGroup($id));
				break;
			case 'Roles' :
				$users = array_merge($users, static::getUsersByRole($id));
				break;
			case 'RoleAndSubordinates' :
				$users = array_merge($users, static::getUsersByRoleAndSubordinate($id));
				break;
		}
		return static::$usersByMemberCache[$member] = array_unique($users);
	}

	protected static $usersByGroupCache = [];

	/**
	 * Get list of users based on group id
	 * @param int $groupId
	 * @param int $i
	 * @return array
	 */
	public static function getUsersByGroup($groupId, $i = 0)
	{
		if (isset(static::$usersByGroupCache[$roleId])) {
			return static::$usersByGroupCache[$roleId];
		}
		$users = [];
		$adb = \PearDatabase::getInstance();
		//Retreiving from the user2grouptable
		$result = $adb->pquery('select userid from vtiger_users2group where groupid=?', [$groupId]);
		while ($userId = $adb->getSingleValue($result)) {
			$users[] = $userId;
		}
		//Retreiving from the vtiger_group2role
		$result = $adb->pquery('select roleid from vtiger_group2role where groupid=?', [$groupId]);
		while ($roleId = $adb->getSingleValue($result)) {
			$roleUsers = static::getUsersByRole($roleId);
			$users = array_merge($users, $roleUsers);
		}
		//Retreiving from the vtiger_group2rs
		$result = $adb->pquery('select roleandsubid from vtiger_group2rs where groupid=?', [$groupId]);
		while ($roleId = $adb->getSingleValue($result)) {
			$roleUsers = static::getUsersByRoleAndSubordinate($roleId);
			$users = array_merge($users, $roleUsers);
		}
		if ($i < 5) {
			//Retreving from group2group
			$result = $adb->pquery('select containsgroupid from vtiger_group2grouprel where groupid=?', [$groupId]);
			while ($containsGroupId = $adb->getSingleValue($result)) {
				$roleUsers = static::getUsersByGroup($containsGroupId, $i++);
				$users = array_merge($users, $roleUsers);
			}
		} else {
			\App\Log::warning('Exceeded the recursive limit, a loop might have been created. Group ID:' . $groupId);
		}
		return static::$usersByGroupCache[$groupId] = array_unique($users);
	}

	protected static $usersBySubordinateCache = [];

	/**
	 * Function to get the vtiger_role and subordinate vtiger_users
	 * @param $roleid -- RoleId :: Type varchar
	 * @returns $roleSubUsers-- Role and Subordinates Related Users Array in the following format:
	 */
	public static function getUsersByRoleAndSubordinate($roleId)
	{
		if (isset(static::$usersBySubordinateCache[$roleId])) {
			return static::$usersBySubordinateCache[$roleId];
		}
		$adb = \PearDatabase::getInstance();
		$roleInfo = static::getRoleDetail($roleId);
		$parentRole = $roleInfo['parentrole'];
		$query = "SELECT vtiger_user2role.userid FROM vtiger_user2role INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole LIKE ?";
		$result = $adb->pquery($query, [$parentRole . '%']);
		$users = [];
		while ($userId = $adb->getSingleValue($result)) {
			$users[] = $userId;
		}
		static::$usersBySubordinateCache[$roleId] = $users;
		return $users;
	}

	protected static $roleInfoCache = [];

	/**
	 * Function to get the vtiger_role information of the specified vtiger_role
	 * @param $roleid -- RoleId :: Type varchar
	 * @returns $roleInfoArray-- RoleInfoArray in the following format:
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
	 * Function to get the role name
	 * @param int $roleId
	 * @return string
	 */
	public static function getRoleName($roleId)
	{
		$roleInfo = static::getRoleDetail($roleId);
		return $roleInfo['rolename'];
	}

	/**
	 * To retreive the parent vtiger_role of the specified vtiger_role
	 * @param $roleid -- The Role Id:: Type varchar
	 * @return  parent vtiger_role array in the following format:
	 */
	public static function getParentRole($roleId)
	{
		$roleInfo = static::getRoleDetail($roleId);
		return $roleInfo['parentRoles'];
	}

	/**
	 * To retreive the subordinate vtiger_roles of the specified parent vtiger_role
	 * @param int $roleId
	 * @return array
	 */
	public static function getRoleSubordinates($roleId)
	{
		if (\App\Cache::has('getRoleSubordinates', $roleId)) {
			return \App\Cache::get('getRoleSubordinates', $roleId);
		}
		$roleDetails = static::getRoleDetail($roleId);
		$roleSubordinates = (new \App\Db\Query())
			->select(['roleid'])
			->from('vtiger_role')
			->where(['like', 'parentrole', $roleDetails['parentrole'] . '::%', false])
			->column();

		\App\Cache::save('getRoleSubordinates', $roleId, $roleSubordinates, \App\Cache::LONG);
		return $roleSubordinates;
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
	 * Get data share
	 * @param int $tabId
	 * @param int $roleId
	 * @return array
	 */
	public static function getDatashare($type, $tabId, $data)
	{
		$cacheKey = "$type|$tabId|" . (is_array($data) ? implode(',', $data) : $data);
		if (\App\Cache::staticHas('getDatashare', $cacheKey)) {
			return \App\Cache::staticGet('getDatashare', $cacheKey);
		}
		$structure = self::$dataShareStructure[$type];
		$query = (new \App\Db\Query())->select([$structure[0] . '.*'])->from($structure[0])
			->innerJoin('vtiger_datashare_module_rel', "$structure[0].shareid = vtiger_datashare_module_rel.shareid")
			->where(['vtiger_datashare_module_rel.tabid' => $tabId]);
		if ($data) {
			$query->andWhere([$structure[1] => $data]);
		}
		$rows = $query->all();
		\App\Cache::staticSave('getDatashare', $cacheKey, $rows);
		return $rows;
	}

	/**
	 * Gives an array which contains the information for what all roles, groups and user data is to be shared with the spcified user for the specified module
	 * @param string $module module name
	 * @param int $userid user id
	 * @param array $defOrgShare default organization sharing permission array
	 * @param string $currentUserRoles roleid
	 * @param string $parentRoles parent roles
	 * @param int $currentUserGroups user id
	 * @return array array which contains the id of roles,group and users data shared with specifed user for the specified module
	 */
	public static function getUserModuleSharingObjects($module, $userid, $defOrgShare, $currentUserRoles, $parentRoles, $currentUserGroups)
	{
		$modTabId = \App\Module::getModuleId($module);
		$modShareWritePermission = $modShareReadPermission = ['ROLE' => [], 'GROUP' => []];
		$modDefOrgShare = $defOrgShare[$modTabId];
		$shareIdMembers = [];
		//If Sharing of leads is Private
		if ($modDefOrgShare === 3 || $modDefOrgShare === 0) {
			$roleWritePer = $roleWritePer = $rsWritePer = $grpReadPer = $grpWritePer = $roleReadPer = [];
			//Retreiving from vtiger_role to vtiger_role
			$rows = static::getDatashare('role2role', $modTabId, $currentUserRoles);
			foreach ($rows as &$row) {
				$shareRoleId = $row['share_roleid'];
				$shareIdRoleMembers = [];
				$shareIdRoles = [];
				$shareIdRoles[] = $shareRoleId;
				$shareIdRoleMembers['ROLE'] = $shareIdRoles;
				$shareIdMembers[$row['shareid']] = $shareIdRoleMembers;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					}
					if (!isset($role_write_per[$shareRoleId])) {
						$roleWritePer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($roleReadPer[$shareRoleId])) {
						$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
					}
				}
			}
			//Retreiving from role to rs
			$parRoleList = [];
			foreach ($parentRoles as $par_role_id) {
				array_push($parRoleList, $par_role_id);
			}
			array_push($parRoleList, $currentUserRoles);
			$rows = static::getDatashare('role2rs', $modTabId, $parRoleList);
			foreach ($rows as &$row) {
				$shareRoleId = $row['share_roleid'];
				$shareIdRoleMembers = [];
				$shareIdRoles = [];
				$shareIdRoles[] = $shareRoleId;
				$shareIdRoleMembers['ROLE'] = $shareIdRoles;
				$shareIdMembers[$row['shareid']] = $shareIdRoleMembers;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					}
					if (!isset($role_write_per[$shareRoleId])) {
						$roleWritePer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($roleReadPer[$shareRoleId])) {
						$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
					}
				}
			}
			//Get roles from Role2Grp
			$groupList = $currentUserGroups;
			if (empty($groupList)) {
				$groupList = [0];
			}
			if ($groupList) {
				$rows = static::getDatashare('role2group', $modTabId, $groupList);
				foreach ($rows as &$row) {
					$shareRoleId = $row['share_roleid'];
					$shareIdRoleMembers = [];
					$shareIdRoles = [];
					$shareIdRoles[] = $shareRoleId;
					$shareIdRoleMembers['ROLE'] = $shareIdRoles;
					$shareIdMembers[$row['shareid']] = $shareIdRoleMembers;
					if ($row['permission'] === 1) {
						if ($modDefOrgShare === 3) {
							if (!isset($roleReadPer[$shareRoleId])) {
								$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
							}
						}
						if (!isset($role_write_per[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					}
				}
			}
			//Get roles from Role2Us
			if (!empty($userid)) {
				$rows = static::getDatashare('role2user', $modTabId, $userid);
				foreach ($rows as &$row) {
					$shareRoleId = $row['share_roleid'];
					$shareIdRoleMembers = [];
					$shareIdRoles = [];
					$shareIdRoles[] = $shareRoleId;
					$shareIdRoleMembers['ROLE'] = $shareIdRoles;
					$shareIdMembers[$row['shareid']] = $shareIdRoleMembers;
					if ($row['permission'] === 1) {
						if ($modDefOrgShare === 3) {
							if (!isset($roleReadPer[$shareRoleId])) {
								$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
							}
						}
						if (!isset($role_write_per[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					}
				}
			}
			//Retreiving from rs to vtiger_role
			$rows = static::getDatashare('rs2role', $modTabId, $currentUserRoles);
			foreach ($rows as &$row) {
				$shareRoleId = $row['share_roleid'];
				$shareRoleIds = getRoleAndSubordinatesRoleIds($row['share_roleandsubid']);
				$shareIdRoleMembers = [];
				$shareIdRoles = [];
				foreach ($shareRoleIds as &$shareRoleId) {
					$shareIdRoles[] = $shareRoleId;
					if ($row['permission'] === 1) {
						if ($modDefOrgShare === 3) {
							if (!isset($roleReadPer[$shareRoleId])) {
								$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
							}
						}
						if (!isset($role_write_per[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					}
				}
				$shareIdRoleMembers['ROLE'] = $shareIdRoles;
				$shareIdMembers[$row['shareid']] = $shareIdRoleMembers;
			}
			//Retreiving from rs to rs
			$rows = static::getDatashare('rs2rs', $modTabId, $parRoleList);
			foreach ($rows as &$row) {
				$share_rsid = $row['share_roleandsubid'];
				$shareRoleIds = getRoleAndSubordinatesRoleIds($share_rsid);
				$shareIdRoleMembers = [];
				$shareIdRoles = [];
				foreach ($shareRoleIds as &$shareRoleId) {
					$shareIdRoles[] = $shareRoleId;
					if ($row['permission'] === 1) {
						if ($modDefOrgShare === 3) {
							if (!isset($roleReadPer[$shareRoleId])) {
								$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
							}
						}
						if (!isset($role_write_per[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					}
				}
				$shareIdRoleMembers['ROLE'] = $shareIdRoles;
				$shareIdMembers[$row['shareid']] = $shareIdRoleMembers;
			}
			//Get roles from Rs2Grp 
			$rows = static::getDatashare('rs2group', $modTabId, $groupList);
			foreach ($rows as &$row) {
				$shareRoleIds = getRoleAndSubordinatesRoleIds($share_rsid);
				$shareIdRoleMembers = [];
				$shareIdRoles = [];
				foreach ($shareRoleIds as &$shareRoleId) {
					$shareIdRoles[] = $shareRoleId;
					if ($row['permission'] === 1) {
						if ($modDefOrgShare === 3) {
							if (!isset($roleReadPer[$shareRoleId])) {
								$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
							}
						}
						if (!isset($role_write_per[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					}
				}
				$shareIdRoleMembers['ROLE'] = $shareIdRoles;
				$shareIdMembers[$row['shareid']] = $shareIdRoleMembers;
			}
			//Get roles from Rs2Us 
			$rows = static::getDatashare('rs2user', $modTabId, $userid);
			foreach ($rows as &$row) {
				$share_rsid = $row['share_roleandsubid'];
				$shareRoleIds = getRoleAndSubordinatesRoleIds($share_rsid);
				$shareIdRoleMembers = [];
				$shareIdRoles = [];
				foreach ($shareRoleIds as &$shareRoleId) {
					$shareIdRoles[] = $shareRoleId;
					if ($row['permission'] === 1) {
						if ($modDefOrgShare === 3) {
							if (!isset($roleReadPer[$shareRoleId])) {
								$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
							}
						}
						if (!isset($role_write_per[$shareRoleId])) {
							$roleWritePer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
						if (!isset($roleReadPer[$shareRoleId])) {
							$roleReadPer[$shareRoleId] = \App\PrivilegeUtil::getUsersByRole($shareRoleId);
						}
					}
				}
				$shareIdRoleMembers['ROLE'] = $shareIdRoles;
				$shareIdMembers[$row['shareid']] = $shareIdRoleMembers;
			}
			$modShareReadPermission['ROLE'] = $roleReadPer;
			$modShareWritePermission['ROLE'] = $roleWritePer;

			//Retreiving from the grp2role sharing 
			$rows = static::getDatashare('group2role', $modTabId, $currentUserRoles);
			foreach ($rows as &$row) {
				$shareGrpId = $row['share_groupid'];
				$shareIdGrpMembers = [];
				$shareIdGrps = [];
				$shareIdGrps[] = $shareGrpId;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($grpReadPer[$shareGrpId])) {
							$focusGrpUsers = new \GetGroupUsers();
							$focusGrpUsers->getAllUsersInGroup($shareGrpId);
							$grpReadPer[$shareGrpId] = $focusGrpUsers->group_users;
							foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
								if (!isset($grpReadPer[$subgrpid])) {
									$grpReadPer[$subgrpid] = $subgrpusers;
								}
								if (!in_array($subgrpid, $shareIdGrps)) {
									$shareIdGrps[] = $subgrpid;
								}
							}
						}
					}
					if (!isset($grpWritePer[$shareGrpId])) {
						$focusGrpUsers = new \GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($shareGrpId);
						$grpWritePer[$shareGrpId] = $focusGrpUsers->group_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!isset($grpWritePer[$subgrpid])) {
								$grpWritePer[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($grpReadPer[$shareGrpId])) {
						$focusGrpUsers = new \GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($shareGrpId);
						$grpReadPer[$shareGrpId] = $focusGrpUsers->group_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				}
				$shareIdGrpMembers['GROUP'] = $shareIdGrps;
				$shareIdMembers[$row['shareid']] = $shareIdGrpMembers;
			}

			//Retreiving from the grp2rs sharing 
			$rows = static::getDatashare('group2rs', $modTabId, $parRoleList);
			foreach ($rows as &$row) {
				$shareGrpId = $row['share_groupid'];
				$shareIdGrpMembers = [];
				$shareIdGrps = [];
				$shareIdGrps[] = $shareGrpId;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($grpReadPer[$shareGrpId])) {
							$focusGrpUsers = new \GetGroupUsers();
							$focusGrpUsers->getAllUsersInGroup($shareGrpId);
							$grpReadPer[$shareGrpId] = $focusGrpUsers->group_users;
							foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
								if (!isset($grpReadPer[$subgrpid])) {
									$grpReadPer[$subgrpid] = $subgrpusers;
								}
								if (!in_array($subgrpid, $shareIdGrps)) {
									$shareIdGrps[] = $subgrpid;
								}
							}
						}
					}
					if (!isset($grpWritePer[$shareGrpId])) {
						$focusGrpUsers = new \GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($shareGrpId);
						$grpWritePer[$shareGrpId] = $focusGrpUsers->group_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!isset($grpWritePer[$subgrpid])) {
								$grpWritePer[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($grpReadPer[$shareGrpId])) {
						$focusGrpUsers = new \GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($shareGrpId);
						$grpReadPer[$shareGrpId] = $focusGrpUsers->group_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				}
				$shareIdGrpMembers['GROUP'] = $shareIdGrps;
				$shareIdMembers[$row['shareid']] = $shareIdGrpMembers;
			}

			//Retreiving from the grp2us sharing 
			$rows = static::getDatashare('group2user', $modTabId, $userid);
			foreach ($rows as &$row) {
				$shareGrpId = $row['share_groupid'];
				$shareIdGrpMembers = [];
				$shareIdGrps = [];
				$shareIdGrps[] = $shareGrpId;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($grpReadPer[$shareGrpId])) {
							$focusGrpUsers = new \GetGroupUsers();
							$focusGrpUsers->getAllUsersInGroup($shareGrpId);
							$grpReadPer[$shareGrpId] = $focusGrpUsers->group_users;
							foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
								if (!isset($grpReadPer[$subgrpid])) {
									$grpReadPer[$subgrpid] = $subgrpusers;
								}
								if (!in_array($subgrpid, $shareIdGrps)) {
									$shareIdGrps[] = $subgrpid;
								}
							}
						}
					}
					if (!isset($grpWritePer[$shareGrpId])) {
						$focusGrpUsers = new \GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($shareGrpId);
						$grpWritePer[$shareGrpId] = $focusGrpUsers->group_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!isset($grpWritePer[$subgrpid])) {
								$grpWritePer[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($grpReadPer[$shareGrpId])) {
						$focusGrpUsers = new \GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($shareGrpId);
						$grpReadPer[$shareGrpId] = $focusGrpUsers->group_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				}
				$shareIdGrpMembers['GROUP'] = $shareIdGrps;
				$shareIdMembers[$row['shareid']] = $shareIdGrpMembers;
			}

			//Retreiving from the grp2grp sharing 
			$rows = static::getDatashare('group2group', $modTabId, $groupList);
			foreach ($rows as &$row) {
				$shareGrpId = $row['share_groupid'];
				$shareIdGrpMembers = [];
				$shareIdGrps = [];
				$shareIdGrps[] = $shareGrpId;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($grpReadPer[$shareGrpId])) {
							$focusGrpUsers = new \GetGroupUsers();
							$focusGrpUsers->getAllUsersInGroup($shareGrpId);
							$grpReadPer[$shareGrpId] = $focusGrpUsers->group_users;
							foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
								if (!isset($grpReadPer[$subgrpid])) {
									$grpReadPer[$subgrpid] = $subgrpusers;
								}
								if (!in_array($subgrpid, $shareIdGrps)) {
									$shareIdGrps[] = $subgrpid;
								}
							}
						}
					}
					if (!isset($grpWritePer[$shareGrpId])) {
						$focusGrpUsers = new \GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($shareGrpId);
						$grpWritePer[$shareGrpId] = $focusGrpUsers->group_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!isset($grpWritePer[$subgrpid])) {
								$grpWritePer[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($grpReadPer[$shareGrpId])) {
						$focusGrpUsers = new \GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup($shareGrpId);
						$grpReadPer[$shareGrpId] = $focusGrpUsers->group_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!isset($grpReadPer[$subgrpid])) {
								$grpReadPer[$subgrpid] = $subgrpusers;
							}
							if (!in_array($subgrpid, $shareIdGrps)) {
								$shareIdGrps[] = $subgrpid;
							}
						}
					}
				}
				$shareIdGrpMembers['GROUP'] = $shareIdGrps;
				$shareIdMembers[$row['shareid']] = $shareIdGrpMembers;
			}

			//Get roles from Us2Us 
			$rows = static::getDatashare('user2user', $modTabId, $userid);
			foreach ($rows as &$row) {
				$shareUserId = $row['share_userid'];
				$shareIdGrpMembers = [];
				$shareIdUsers = [];
				$shareIdUsers[] = $shareUserId;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($grpReadPer[$shareUserId])) {
							$grpReadPer[$shareUserId] = [$shareUserId];
						}
					}
					if (!isset($grpWritePer[$shareUserId])) {
						$grpWritePer[$shareUserId] = [$shareUserId];
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
				}
				$shareIdGrpMembers['GROUP'] = $shareIdUsers;
				$shareIdMembers[$row['shareid']] = $shareIdGrpMembers;
			}
			//Get roles from Us2Grp 
			$rows = static::getDatashare('user2group', $modTabId, $groupList);
			foreach ($rows as &$row) {
				$shareUserId = $row['share_userid'];
				$shareIdGrpMembers = [];
				$shareIdUsers = [];
				$shareIdUsers[] = $shareUserId;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($grpReadPer[$shareUserId])) {
							$grpReadPer[$shareUserId] = [$shareUserId];
						}
					}
					if (!isset($grpWritePer[$shareUserId])) {
						$grpWritePer[$shareUserId] = [$shareUserId];
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
				}
				$shareIdGrpMembers['GROUP'] = $shareIdUsers;
				$shareIdMembers[$row['shareid']] = $shareIdGrpMembers;
			}
			//Get roles from Us2role 
			$rows = static::getDatashare('user2role', $modTabId, $currentUserRoles);
			foreach ($rows as &$row) {
				$shareUserId = $row['share_userid'];
				$shareIdGrpMembers = [];
				$shareIdUsers = [];
				$shareIdUsers[] = $shareUserId;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($grpReadPer[$shareUserId])) {
							$grpReadPer[$shareUserId] = [$shareUserId];
						}
					}
					if (!isset($grpWritePer[$shareUserId])) {
						$grpWritePer[$shareUserId] = [$shareUserId];
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
				}
				$shareIdGrpMembers['GROUP'] = $shareIdUsers;
				$shareIdMembers[$row['shareid']] = $shareIdGrpMembers;
			}

			//Get roles from Us2rs 
			$rows = static::getDatashare('user2rs', $modTabId, $parRoleList);
			foreach ($rows as &$row) {
				$shareUserId = $row['share_userid'];
				$shareIdGrpMembers = [];
				$shareIdUsers = [];
				$shareIdUsers[] = $shareUserId;
				if ($row['permission'] === 1) {
					if ($modDefOrgShare === 3) {
						if (!isset($grpReadPer[$shareUserId])) {
							$grpReadPer[$shareUserId] = [$shareUserId];
						}
					}
					if (!isset($grpWritePer[$shareUserId])) {
						$grpWritePer[$shareUserId] = [$shareUserId];
					}
				} elseif ($row['permission'] === 0 && $modDefOrgShare === 3) {
					if (!isset($grpReadPer[$shareUserId])) {
						$grpReadPer[$shareUserId] = [$shareUserId];
					}
				}
				$shareIdGrpMembers['GROUP'] = $shareIdUsers;
				$shareIdMembers[$row['shareid']] = $shareIdGrpMembers;
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
}
