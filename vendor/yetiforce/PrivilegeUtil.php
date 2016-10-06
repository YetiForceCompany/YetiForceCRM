<?php namespace App;

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
		$entId = self::$fn_name($recordId);
		if ($entId != '') {
			$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($entId);
			if ($recordMetaData) {
				$ownerId = $recordMetaData['smownerid'];
				$type = \includes\fields\Owner::getType($ownerId);
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
	public static function &getDatashareRelatedModules()
	{
		if (self::$datashareRelatedCache === false) {
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
			self::$datashareRelatedCache = $relModSharArr;
		}
		return self::$datashareRelatedCache;
	}

	protected static $defaultSharingActionCache = false;

	/**
	 * This Function returns the Default Organisation Sharing Action Array for all modules
	 * @return array
	 */
	public static function &getAllDefaultSharingAction()
	{
		if (self::$defaultSharingActionCache === false) {
			\App\Log::trace('getAllDefaultSharingAction');
			$adb = \PearDatabase::getInstance();
			$copy = [];
			//retreiving the standard permissions
			$result = $adb->query('select * from vtiger_def_org_share');
			while ($row = $adb->getRow($result)) {
				$copy[$row['tabid']] = $row['permission'];
			}
			self::$defaultSharingActionCache = $copy;
		}
		return self::$defaultSharingActionCache;
	}

	protected static $usersByRoleCache = [];

	/**
	 * Function to get the vtiger_role related user ids
	 * @param int $roleId RoleId :: Type varchar
	 * @return array $users -- Role Related User Array in the following format:
	 */
	public static function &getUsersByRole($roleId)
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

	const MEMBER_TYPE_USERS = 'Users';
	const MEMBER_TYPE_GROUPS = 'Groups';
	const MEMBER_TYPE_ROLES = 'Roles';
	const MEMBER_TYPE_ROLE_AND_SUBORDINATES = 'RoleAndSubordinates';

	protected static $membersCache = false;

	/**
	 * Function to get all members
	 * @return array
	 */
	public static function &getMembers()
	{
		if (self::$membersCache === false) {
			$members = [];
			$owner = new \includes\fields\Owner();
			foreach ($owner->initUsers() as $id => $user) {
				$members[self::MEMBER_TYPE_USERS][self::MEMBER_TYPE_USERS . ':' . $id] = ['name' => $user['fullName'], 'id' => $id, 'type' => self::MEMBER_TYPE_USERS];
			}
			foreach ($owner->getGroups(false) as $id => $groupName) {
				$members[self::MEMBER_TYPE_GROUPS][self::MEMBER_TYPE_GROUPS . ':' . $id] = ['name' => $groupName, 'id' => $id, 'type' => self::MEMBER_TYPE_GROUPS];
			}
			foreach (\Settings_Roles_Record_Model::getAll() as $id => $roleModel) {
				$members[self::MEMBER_TYPE_ROLES][self::MEMBER_TYPE_ROLES . ':' . $id] = ['name' => $roleModel->getName(), 'id' => $id, 'type' => self::MEMBER_TYPE_ROLES];
				$members[self::MEMBER_TYPE_ROLE_AND_SUBORDINATES][self::MEMBER_TYPE_ROLE_AND_SUBORDINATES . ':' . $id] = ['name' => $roleModel->getName(), 'id' => $id, 'type' => self::MEMBER_TYPE_ROLE_AND_SUBORDINATES];
			}
			self::$membersCache = $members;
		}
		return self::$membersCache;
	}

	protected static $usersByMemberCache = [];

	/**
	 * Get list of users based on members, eg. Users:2, Roles:H2
	 * @param string $member
	 * @return array
	 */
	public static function &getUserByMember($member)
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
		$users = array_unique($users);
		static::$usersByMemberCache[$member] = $users;
		return $users;
	}

	protected static $usersByGroupCache = [];

	/**
	 * Get list of users based on group id
	 * @param int $groupId
	 * @param int $i
	 * @return array
	 */
	public static function &getUsersByGroup($groupId, $i = 0)
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
			\App\log::warning('Exceeded the recursive limit, a loop might have been created. Group ID:' . $groupId);
		}
		$users = array_unique($users);
		static::$usersByGroupCache[$groupId] = $users;
		return $users;
	}

	protected static $usersBySubordinateCache = [];

	/**
	 * Function to get the vtiger_role and subordinate vtiger_users
	 * @param $roleid -- RoleId :: Type varchar
	 * @returns $roleSubUsers-- Role and Subordinates Related Users Array in the following format:
	 */
	public static function &getUsersByRoleAndSubordinate($roleId)
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
	public static function &getRoleDetail($roleId)
	{
		if (isset(static::$roleInfoCache[$roleId])) {
			return static::$roleInfoCache[$roleId];
		}
		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery('select * from vtiger_role where roleid=?', [$roleId]);
		$row = $adb->getRow($result);
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
}
