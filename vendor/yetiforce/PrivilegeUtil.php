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
		if (Cache::staticHas('getUserGroups', $userId)) {
			return Cache::staticGet('getUserGroups', $userId);
		}
		$groupIds = (new \App\Db\Query())->select('groupid')
			->from('vtiger_users2group')
			->where(['userid' => $userId])
			->column();
		Cache::staticSave('getUserGroups', $userId, $groupIds);
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
		if (isset(static::$roleInfoCache[$roleId])) {
			return static::$roleInfoCache[$roleId];
		}
		$row = (new Db\Query())->from('vtiger_role')->where(['roleid' => $roleId])->one();
		if ($row) {
			$parentRoleArr = explode('::', $row['parentrole']);
			array_pop($parentRoleArr);
			$row['parentRoles'] = $parentRoleArr;
			$immediateParent = array_pop($parentRoleArr);
			$row['immediateParent'] = $immediateParent;
		}
		static::$roleInfoCache[$roleId] = $row;
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
		$adb = \PearDatabase::getInstance();

		$mod_tabid = \App\Module::getModuleId($module);
		$mod_share_read_permission = [];
		$mod_share_write_permission = [];
		$mod_share_read_permission['ROLE'] = [];
		$mod_share_write_permission['ROLE'] = [];
		$mod_share_read_permission['GROUP'] = [];
		$mod_share_write_permission['GROUP'] = [];

		$share_id_members = [];
		//If Sharing of leads is Private
		if ($defOrgShare[$mod_tabid] == 3 || $defOrgShare[$mod_tabid] == 0) {
			$role_read_per = [];
			$role_write_per = [];
			$rs_read_per = [];
			$rs_write_per = [];
			$grp_read_per = [];
			$grp_write_per = [];
			//Retreiving from vtiger_role to vtiger_role
			$query = "select vtiger_datashare_role2role.* from vtiger_datashare_role2role inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_role2role.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_role2role.to_roleid=?";
			$result = $adb->pquery($query, array($mod_tabid, $currentUserRoles));
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
					if ($defOrgShare[$mod_tabid] == 3) {
						if (!isset($role_read_per[$share_roleid])) {
							$role_read_per[$share_roleid] = getRoleUserIds($share_roleid);
						}
					}
					if (!isset($role_write_per[$share_roleid])) {
						$role_write_per[$share_roleid] = getRoleUserIds($share_roleid);
					}
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
					if (!isset($role_read_per[$share_roleid])) {
						$role_read_per[$share_roleid] = getRoleUserIds($share_roleid);
					}
				}
			}

			//Retreiving from role to rs
			$parRoleList = array();
			foreach ($parentRoles as $par_role_id) {
				array_push($parRoleList, $par_role_id);
			}
			array_push($parRoleList, $currentUserRoles);
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
					if ($defOrgShare[$mod_tabid] == 3) {
						if (!isset($role_read_per[$share_roleid])) {
							$role_read_per[$share_roleid] = getRoleUserIds($share_roleid);
						}
					}
					if (!isset($role_write_per[$share_roleid])) {
						$role_write_per[$share_roleid] = getRoleUserIds($share_roleid);
					}
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
					if (!isset($role_read_per[$share_roleid])) {
						$role_read_per[$share_roleid] = getRoleUserIds($share_roleid);
					}
				}
			}

			//Get roles from Role2Grp
			$groupList = $currentUserGroups;
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
						if ($defOrgShare[$mod_tabid] == 3) {
							if (!array_key_exists($share_roleid, $role_read_per)) {

								$share_role_users = getRoleUserIds($share_roleid);
								$role_read_per[$share_roleid] = $share_role_users;
							}
						}
						if (!array_key_exists($share_roleid, $role_write_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_write_per[$share_roleid] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
						if (!array_key_exists($share_roleid, $role_read_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_read_per[$share_roleid] = $share_role_users;
						}
					}
				}
			}

			//Get roles from Role2Us
			if (!empty($userid)) {
				$query = 'select vtiger_datashare_role2us.* from vtiger_datashare_role2us inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_role2us.shareid where vtiger_datashare_module_rel.tabid=? AND vtiger_datashare_role2us.to_userid = ?';
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
						if ($defOrgShare[$mod_tabid] == 3) {
							if (!array_key_exists($share_roleid, $role_read_per)) {

								$share_role_users = getRoleUserIds($share_roleid);
								$role_read_per[$share_roleid] = $share_role_users;
							}
						}
						if (!array_key_exists($share_roleid, $role_write_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_write_per[$share_roleid] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
						if (!array_key_exists($share_roleid, $role_read_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_read_per[$share_roleid] = $share_role_users;
						}
					}
				}
			}

			//Retreiving from rs to vtiger_role
			$query = "select vtiger_datashare_rs2role.* from vtiger_datashare_rs2role inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_rs2role.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_rs2role.to_roleid=?";
			$result = $adb->pquery($query, array($mod_tabid, $currentUserRoles));
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
						if ($defOrgShare[$mod_tabid] == 3) {
							if (!array_key_exists($share_roleid, $role_read_per)) {

								$share_role_users = getRoleUserIds($share_roleid);
								$role_read_per[$share_roleid] = $share_role_users;
							}
						}
						if (!array_key_exists($share_roleid, $role_write_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_write_per[$share_roleid] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
			foreach ($parentRoles as $par_role_id) {
				array_push($parRoleList, $par_role_id);
			}
			array_push($parRoleList, $currentUserRoles);
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
						if ($defOrgShare[$mod_tabid] == 3) {
							if (!array_key_exists($share_roleid, $role_read_per)) {

								$share_role_users = getRoleUserIds($share_roleid);
								$role_read_per[$share_roleid] = $share_role_users;
							}
						}
						if (!array_key_exists($share_roleid, $role_write_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_write_per[$share_roleid] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
						if ($defOrgShare[$mod_tabid] == 3) {
							if (!array_key_exists($share_roleid, $role_read_per)) {

								$share_role_users = getRoleUserIds($share_roleid);
								$role_read_per[$share_roleid] = $share_role_users;
							}
						}
						if (!array_key_exists($share_roleid, $role_write_per)) {

							$share_role_users = getRoleUserIds($share_roleid);
							$role_write_per[$share_roleid] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
			$query = 'select vtiger_datashare_rs2us.* from vtiger_datashare_rs2us inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_rs2us.shareid where vtiger_datashare_module_rel.tabid=? AND to_userid=?';
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
						if ($defOrgShare[$mod_tabid] == 3) {
							if (!array_key_exists($share_roleid, $role_read_per)) {
								$share_role_users = getRoleUserIds($share_roleid);
								$role_read_per[$share_roleid] = $share_role_users;
							}
						}
						if (!array_key_exists($share_roleid, $role_write_per)) {
							$share_role_users = getRoleUserIds($share_roleid);
							$role_write_per[$share_roleid] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
			$result = $adb->pquery($query, array($mod_tabid, $currentUserRoles));
			$num_rows = $adb->num_rows($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_grpid = $adb->query_result($result, $i, 'share_groupid');
				$share_permission = $adb->query_result($result, $i, 'permission');

				$shareid = $adb->query_result($result, $i, 'shareid');
				$share_id_grp_members = [];
				$share_id_grps = [];
				$share_id_grps[] = $share_grpid;


				if ($share_permission == 1) {
					if ($defOrgShare[$mod_tabid] == 3) {
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
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
					if ($defOrgShare[$mod_tabid] == 3) {
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
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
					if ($defOrgShare[$mod_tabid] == 3) {
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
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
					if ($defOrgShare[$mod_tabid] == 3) {
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
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
			$query = 'select vtiger_datashare_us2us.* from vtiger_datashare_us2us inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_us2us.shareid where vtiger_datashare_module_rel.tabid=? AND to_userid=?';
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
					if ($defOrgShare[$mod_tabid] == 3) {
						if (!array_key_exists($share_userid, $grp_read_per)) {
							$grp_read_per[$share_userid] = [$share_userid];
						}
					}
					if (!array_key_exists($share_userid, $grp_write_per)) {
						$grp_write_per[$share_userid] = [$share_userid];
					}
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
					if ($defOrgShare[$mod_tabid] == 3) {
						if (!array_key_exists($share_userid, $grp_read_per)) {
							$grp_read_per[$share_userid] = [$share_userid];
						}
					}
					if (!array_key_exists($share_userid, $grp_write_per)) {
						$grp_write_per[$share_userid] = [$share_userid];
					}
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
					if (!array_key_exists($share_userid, $grp_read_per)) {
						$grp_read_per[$share_userid] = [$share_userid];
					}
				}
				$share_id_grp_members['GROUP'] = $share_id_users;
				$share_id_members[$shareid] = $share_id_grp_members;
			}

			//Get roles from Us2role
			$query = 'select vtiger_datashare_us2role.* from vtiger_datashare_us2role inner join vtiger_datashare_module_rel on vtiger_datashare_module_rel.shareid=vtiger_datashare_us2role.shareid where vtiger_datashare_module_rel.tabid=? and vtiger_datashare_us2role.to_roleid=?';
			$qparams = array($mod_tabid, $currentUserRoles);

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
					if ($defOrgShare[$mod_tabid] == 3) {
						if (!array_key_exists($share_userid, $grp_read_per)) {
							$grp_read_per[$share_userid] = [$share_userid];
						}
					}
					if (!array_key_exists($share_userid, $grp_write_per)) {
						$grp_write_per[$share_userid] = [$share_userid];
					}
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
					if ($defOrgShare[$mod_tabid] == 3) {
						if (!array_key_exists($share_userid, $grp_read_per)) {
							$grp_read_per[$share_userid] = [$share_userid];
						}
					}
					if (!array_key_exists($share_userid, $grp_write_per)) {
						$grp_write_per[$share_userid] = [$share_userid];
					}
				} elseif ($share_permission == 0 && $defOrgShare[$mod_tabid] == 3) {
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
		return [
			'read' => $mod_share_read_permission,
			'write' => $mod_share_write_permission,
			'sharingrules' => $share_id_members,
		];
	}
}
