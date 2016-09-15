<?php namespace includes\fields;

/**
 * Owner class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Owner
{

	protected $moduleName;
	protected $searchValue;
	protected $currentUser;

	public static function getInstance($moduleName = false, $currentUser = false)
	{
		if ($currentUser && $currentUser instanceof Users) {
			$currentUser = \Users_Record_Model::getInstanceFromUserObject($currentUser);
		} elseif ($currentUser == false) {
			$currentUser = \Users_Record_Model::getCurrentUserModel();
		}
		$cacheKey = $moduleName . $currentUser->getId();
		$instance = \Vtiger_Cache::get('includes\fields\Owner', $cacheKey);
		if ($instance === false) {
			$instance = new self();
			$instance->moduleName = $moduleName != false ? $moduleName : \AppRequest::get('module');
			$instance->currentUser = $currentUser;
			\Vtiger_Cache::set('includes\fields\Owner', $cacheKey, $instance);
		}
		return $instance;
	}

	public function find($value)
	{
		$this->searchValue = $value;
	}

	/**
	 * Function to get all the accessible groups
	 * @return <Array>
	 */
	public function getAccessibleGroups($private = '', $fieldType = false, $translate = false)
	{
		$cacheKey = $private . $this->moduleName . $fieldType;
		$accessibleGroups = \Vtiger_Cache::get('getAccessibleGroups', $cacheKey);
		if ($accessibleGroups === false) {
			$currentUserRoleModel = \Settings_Roles_Record_Model::getInstanceById($this->currentUser->getRole());
			if (!empty($fieldType) && $currentUserRoleModel->get('allowassignedrecordsto') == '5' && $private != 'Public') {
				$accessibleGroups = $this->getAllocation('groups', $private, $fieldType);
			} else {
				$accessibleGroups = $this->getGroups(false, 'Active', '', $private);
			}
			\Vtiger_Cache::set('getAccessibleGroups', $cacheKey, $accessibleGroups);
		}
		if ($translate) {
			foreach ($accessibleGroups as &$name) {
				$name = vtranslate($name);
			}
		}
		if (!empty($this->searchValue)) {
			$this->searchValue = strtolower($this->searchValue);
			$accessibleGroups = array_filter($accessibleGroups, function($name) {
				return strstr(strtolower($name), $this->searchValue);
			});
		}
		return $accessibleGroups;
	}

	/**
	 * Function to get all the accessible users
	 * @return <Array>
	 */
	public function getAccessibleUsers($private = '', $fieldType = false)
	{
		$cacheKey = $private . $this->moduleName . $fieldType . $fieldType;
		$accessibleUser = \Vtiger_Cache::get('getAccessibleUsers', $cacheKey);
		if ($accessibleUser === false) {
			$currentUserRoleModel = \Settings_Roles_Record_Model::getInstanceById($this->currentUser->getRole());
			if ($currentUserRoleModel->get('allowassignedrecordsto') == '1' || $private == 'Public') {
				$accessibleUser = $this->getUsers(false, 'Active', '', $private, true);
			} else if ($currentUserRoleModel->get('allowassignedrecordsto') == '2') {
				$accessibleUser = $this->getSameLevelUsersWithSubordinates();
			} else if ($currentUserRoleModel->get('allowassignedrecordsto') == '3') {
				$accessibleUser = $this->getRoleBasedSubordinateUsers();
			} else if (!empty($fieldType) && $currentUserRoleModel->get('allowassignedrecordsto') == '5') {
				$accessibleUser = $this->getAllocation('users', '', $fieldType);
			} else {
				$accessibleUser[$this->currentUser->getId()] = $this->currentUser->getName();
			}
			\Vtiger_Cache::set('getAccessibleUsers', $cacheKey, $accessibleUser);
		}
		return $accessibleUser;
	}

	public function getAccessible($private = '', $fieldType = false, $translate = false)
	{
		return [
			'users' => $this->getAccessibleUsers($private, $fieldType),
			'groups' => $this->getAccessibleGroups($private, $fieldType, $translate)
		];
	}

	/**
	 * Function to get same level and subordinates Users
	 * @return <array> Users
	 */
	public function getSameLevelUsersWithSubordinates()
	{
		$currentUserRoleModel = \Settings_Roles_Record_Model::getInstanceById($this->currentUser->getRole());
		$sameLevelRoles = $currentUserRoleModel->getSameLevelRoles();
		$sameLevelUsers = $this->getAllUsersOnRoles($sameLevelRoles);
		$subordinateUsers = $this->getRoleBasedSubordinateUsers();
		foreach ($subordinateUsers as $userId => $userName) {
			$sameLevelUsers[$userId] = $userName;
		}
		return $sameLevelUsers;
	}

	/**
	 * Function to get subordinates Users
	 * @return <array> Users
	 */
	public function getRoleBasedSubordinateUsers()
	{
		$currentUserRoleModel = \Settings_Roles_Record_Model::getInstanceById($this->currentUser->getRole());
		$childernRoles = $currentUserRoleModel->getAllChildren();
		$users = $this->getAllUsersOnRoles($childernRoles);
		$currentUserDetail = array($this->currentUser->getId() => $this->currentUser->getDisplayName());
		$users += $currentUserDetail;
		return $users;
	}

	/**
	 * Function to get the users based on Roles
	 * @param type $roles
	 * @return <array>
	 */
	public function getAllUsersOnRoles($roles)
	{
		$roleIds = [];
		foreach ($roles as $key => $role) {
			$roleIds[] = $role->getId();
		}

		if (empty($roleIds)) {
			return [];
		}
		$cacheKey = implode(',', $roleIds);
		$subUsers = \Vtiger_Cache::get('getAllUsersOnRoles', $cacheKey);
		if ($subUsers !== false) {
			return $subUsers;
		}
		$db = \PearDatabase::getInstance();
		$sql = sprintf('SELECT userid FROM vtiger_user2role WHERE roleid IN (%s)', generateQuestionMarks($roleIds));
		$result = $db->pquery($sql, $roleIds);
		$userIds = [];
		$subUsers = [];
		if ($db->getRowCount($result) > 0) {
			while (($userid = $db->getSingleValue($result)) !== false) {
				$userIds[] = $userid;
			}
			$subUsers = $this->getUsers(false, 'Active', $userIds);
		}
		\Vtiger_Cache::set('getAllUsersOnRoles', $cacheKey, $subUsers);
		return $subUsers;
	}

	public function getAllocation($mode, $private = '', $fieldType)
	{
		if (\AppRequest::get('parent') != 'Settings') {
			$moduleName = $this->moduleName;
		}

		$result = [];
		$usersGroups = \Settings_RecordAllocation_Module_Model::getRecordAllocationByModule($fieldType, $moduleName);
		$usersGroups = ($usersGroups && $usersGroups[$this->currentUser->getId()]) ? $usersGroups[$this->currentUser->getId()] : [];
		if ($mode == 'users') {
			$users = $usersGroups ? $usersGroups['users'] : [];
			if (!empty($users)) {
				$result = $this->getUsers(false, 'Active', $users);
			}
		} else {
			$groups = $usersGroups ? $usersGroups['groups'] : [];
			if (!empty($groups)) {
				$groupsAll = $this->getGroups(false, 'Active', '', $private);
				foreach ($groupsAll as $ID => $name) {
					if (in_array($ID, $groups)) {
						$result[$ID] = $name;
					}
				}
			}
		}
		return $result;
	}

	public function initUsers($status = 'Active', $assignedUser = '', $private = '')
	{
		$log = \LoggerManager::getInstance();
		$cacheKeyMod = $private == 'private' ? $this->moduleName : '';
		$cacheKeyAss = is_array($assignedUser) ? md5(json_encode($assignedUser)) : $assignedUser;
		$cacheKey = $cacheKeyMod . $status . $cacheKeyAss . $private;
		$tempResult = \Vtiger_Cache::get('getUsers', $cacheKey);
		if ($tempResult === false) {
			$db = \PearDatabase::getInstance();
			$entityData = \includes\Modules::getEntityInfo('Users');

			// Including deleted vtiger_users for now.
			if ($private == 'private') {
				$userPrivileges = \Vtiger_Util_Helper::getUserPrivilegesFile($this->currentUser->getId());
				$log->debug('Sharing is Private. Only the current user should be listed');
				$query = "SELECT id,%s,is_admin,cal_color,status FROM vtiger_users WHERE id=? UNION SELECT vtiger_user2role.userid AS id,%s,is_admin,cal_color,status FROM vtiger_user2role 
							INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole LIKE ? UNION
							SELECT shareduserid AS id,%s,is_admin,cal_color,status FROM vtiger_tmp_write_user_sharing_per INNER JOIN vtiger_users ON vtiger_users.id=vtiger_tmp_write_user_sharing_per.shareduserid WHERE vtiger_tmp_write_user_sharing_per.userid=? && vtiger_tmp_write_user_sharing_per.tabid=?";
				$params = array($this->currentUser->getId(), $userPrivileges['parent_role_seq'] . '::%', $this->currentUser->getId(), \includes\Modules::getModuleId($this->moduleName));
			} else {
				$log->debug('Sharing is Public. All vtiger_users should be listed');
				$query = 'SELECT id,%s,is_admin,cal_color,status FROM vtiger_users';
				$params = [];
			}
			$where = '';
			$query = str_replace('%s', implode(',', $entityData['fieldnameArr']), $query);
			if (!empty($assignedUser)) {
				if (is_array($assignedUser)) {
					$where .= sprintf(' && id IN (%s)', generateQuestionMarks($assignedUser));
					foreach ($assignedUser as $id) {
						array_push($params, $id);
					}
				} else {
					$where .= ' && id=?';
					array_push($params, $assignedUser);
				}
			}
			if (!empty($this->searchValue)) {
				$entityName = $db->concat($entityData['fieldnameArr']);
				$where .= " && $entityName LIKE ?";
				array_push($params, "%$this->searchValue%");
			}
			if (!empty($where)) {
				$query .= sprintf(' WHERE %s ', ltrim($where, ' &&'));
			}
			$result = $db->pquery($query, $params);
			$tempResult = [];
			// Get the id and the name.
			while ($row = $db->getRow($result)) {
				if ($status == 'Active' && $row['status'] != 'Active') {
					continue;
				}
				$fullName = '';
				foreach ($entityData['fieldnameArr'] as &$field) {
					$fullName .= ' ' . $row[$field];
				}
				$row['fullName'] = trim($fullName);
				$tempResult[$row['id']] = $row;
			}
			\Vtiger_Cache::set('getUsers', $cacheKey, $tempResult);
		}
		return $tempResult;
	}

	/** Function returns the user key in user array
	 * @param $addBlank -- boolean:: Type boolean
	 * @param $status -- user status:: Type string
	 * @param $assignedUser -- user id:: Type string or array
	 * @param $private -- sharing type:: Type string
	 * @param $onlyAdmin -- show only admin users:: Type boolean
	 * @returns $users -- user array:: Type array
	 *
	 */
	public function getUsers($addBlank = false, $status = 'Active', $assignedUser = '', $private = '', $onlyAdmin = false)
	{
		$log = \LoggerManager::getInstance();
		$log->debug("Entering getUsers($addBlank,$status,$assignedUser,$private) method ...");

		$tempResult = $this->initUsers($status, $assignedUser, $private);

		$users = [];
		if ($addBlank == true) {
			// Add in a blank row
			$users[''] = '';
		}
		$adminInList = \AppConfig::performance('SHOW_ADMINISTRATORS_IN_USERS_LIST');
		$isAdmin = $this->currentUser->isAdminUser();
		foreach ($tempResult as $key => $row) {
			if (!$onlyAdmin || $isAdmin || !(!$adminInList && $row['is_admin'] == 'on')) {
				$users[$key] = $row['fullName'];
			}
		}
		asort($users);
		$log->debug('Exiting getUsers method ...');
		return $users;
	}

	public function getGroups($addBlank = true, $status = 'Active', $assignedUser = '', $private = '')
	{
		$log = \LoggerManager::getInstance();
		$log->debug("Entering getGroups($addBlank,$status,$assignedUser,$private) method ...");

		if (\AppRequest::get('parent') != 'Settings' && $this->moduleName) {
			$moduleName = $this->moduleName;
			$tabid = \includes\Modules::getModuleId($moduleName);
		}

		$cacheKey = $addBlank . $status . $assignedUser . $private . $moduleName;
		$tempResult = \Vtiger_Cache::get('getGroups', $cacheKey);
		if ($tempResult !== false) {
			return $tempResult;
		}

		$db = \PearDatabase::getInstance();
		// Including deleted vtiger_users for now.
		$log->debug('Sharing is Public. All vtiger_users should be listed');
		$query = 'SELECT groupid, groupname FROM vtiger_groups';
		$tempResult = $params = [];

		if ($moduleName && $moduleName != 'CustomView') {
			$query .= ' WHERE groupid IN (SELECT groupid FROM vtiger_group2modules WHERE tabid = ?)';
			$params[] = $tabid;
		}
		if ($private == 'private') {
			$userPrivileges = \Vtiger_Util_Helper::getUserPrivilegesFile($this->currentUser->getId());
			if (strpos($query, 'WHERE') === false)
				$query .= ' WHERE';
			else
				$query .= ' AND';
			$query .= ' groupid=?';
			array_push($params, $this->currentUser->getId());

			if (count($userPrivileges['groups']) != 0) {
				$query .= ' || vtiger_groups.groupid in (' . generateQuestionMarks($userPrivileges['groups']) . ')';
				array_push($params, $userPrivileges['groups']);
			}
			$log->debug('Sharing is Private. Only the current user should be listed');
			$query .= ' union select vtiger_group2role.groupid as groupid,vtiger_groups.groupname as groupname from vtiger_group2role inner join vtiger_groups on vtiger_groups.groupid=vtiger_group2role.groupid inner join vtiger_role on vtiger_role.roleid=vtiger_group2role.roleid where vtiger_role.parentrole like ?';
			array_push($params, $userPrivileges['parent_role_seq'] . '::%');

			if (count($userPrivileges['groups']) != 0) {
				$query .= ' union select vtiger_groups.groupid as groupid,vtiger_groups.groupname as groupname from vtiger_groups inner join vtiger_group2rs on vtiger_groups.groupid=vtiger_group2rs.groupid where vtiger_group2rs.roleandsubid in (' . generateQuestionMarks($userPrivileges['parent_roles']) . ')';
				array_push($params, $userPrivileges['parent_roles']);
			}

			$query .= ' union select sharedgroupid as groupid,vtiger_groups.groupname as groupname from vtiger_tmp_write_group_sharing_per inner join vtiger_groups on vtiger_groups.groupid=vtiger_tmp_write_group_sharing_per.sharedgroupid where vtiger_tmp_write_group_sharing_per.userid=?';
			array_push($params, $this->currentUser->getId());

			$query .= ' and vtiger_tmp_write_group_sharing_per.tabid=?';
			array_push($params, $tabid);
		}
		$query .= ' order by groupname ASC';
		$result = $db->pquery($query, $params);

		if ($addBlank == true) {
			// Add in a blank row
			$tempResult[''] = '';
		}

		// Get the id and the name.
		while ($row = $db->getRow($result)) {
			$tempResult[$row['groupid']] = decode_html($row['groupname']);
		}
		\Vtiger_Cache::set('getGroups', $cacheKey, $tempResult);
		$log->debug('Exiting getGroups method ...');
		return $tempResult;
	}

	/**
	 * Function returns List of Accessible Users for a Module
	 * @return <Array of Users_Record_Model>
	 */
	public function getAccessibleGroupForModule()
	{
		$curentUserPrivileges = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($this->currentUser->isAdminUser() || $curentUserPrivileges->hasGlobalWritePermission()) {
			$groups = $this->getAccessibleGroups('');
		} else {
			$sharingAccessModel = \Settings_SharingAccess_Module_Model::getInstance($this->moduleName);
			if ($sharingAccessModel && $sharingAccessModel->isPrivate()) {
				$groups = $this->getAccessibleGroups('private');
			} else {
				$groups = $this->getAccessibleGroups('');
			}
		}
		return $groups;
	}

	/**
	 * Function returns List of Accessible Users for a Module
	 * @param <String> $module
	 * @return <Array of Users_Record_Model>
	 */
	public function getAccessibleUsersForModule()
	{
		$curentUserPrivileges = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($this->currentUser->isAdminUser() || $curentUserPrivileges->hasGlobalWritePermission()) {
			$users = $this->getAccessibleUsers('');
		} else {
			$sharingAccessModel = \Settings_SharingAccess_Module_Model::getInstance($this->moduleName);
			if ($sharingAccessModel && $sharingAccessModel->isPrivate()) {
				$users = $this->getAccessibleUsers('private');
			} else {
				$users = $this->getAccessibleUsers('');
			}
		}
		return $users;
	}

	public function getUsersAndGroupForModuleList($view = false, $conditions = false)
	{
		$db = \PearDatabase::getInstance();
		$queryGenerator = new \QueryGenerator($this->moduleName, $this->currentUser);
		if ($view) {
			$queryGenerator->initForCustomViewById($view);
		}
		if ($conditions) {
			if (!is_array(current($conditions))) {
				$conditions = [$conditions];
			}
			foreach ($conditions as $condition) {
				$conditionParam = ['column' => $condition[0], 'value' => $condition[1], 'operator' => $condition[2], 'glue' => $condition[3]];
				if (isset($condition['tablename'])) {
					$conditionParam['tablename'] = $condition['tablename'];
				}
				$queryGenerator->setCustomCondition($conditionParam);
			}
		}
		$queryGenerator->setFields(['assigned_user_id']);
		$listQuery = $queryGenerator->getQuery('SELECT DISTINCT');
		$result = $db->query($listQuery);
		$ids = $db->getArrayColumn($result);

		$users = $groups = [];
		foreach ($ids as &$id) {
			$name = self::getUserLabel($id);
			if (!empty($name)) {
				$users[$id] = $name;
			}
		}
		$diffIds = array_diff($ids, array_keys($users));
		if ($diffIds) {
			foreach (array_values($diffIds) as $id) {
				$name = self::getGroupName($id);
				if (!empty($name)) {
					$groups[$id] = $name;
				}
			}
		}
		return ['users' => $users, 'group' => $groups];
	}

	public static function getAllUsers($status = 'Active')
	{
		$instance = new self();
		return $instance->initUsers($status);
	}

	protected static $usersIdsCache = [];

	public static function getUsersIds($status = 'Active')
	{
		if (!isset(self::$usersIdsCache[$status])) {
			$rows = [];
			if (\AppConfig::performance('ENABLE_CACHING_USERS')) {
				$rows = \includes\PrivilegeFile::getUser('id');
			} else {
				$instance = new self();
				$rows = $instance->initUsers($status);
			}
			self::$usersIdsCache[$status] = array_keys($rows);
		}
		return self::$usersIdsCache[$status];
	}

	protected static $ownerLabelCache = [];
	protected static $userLabelCache = [];
	protected static $groupLabelCache = [];

	public static function getLabel($mixedId)
	{
		$multiMode = is_array($mixedId);
		$ids = $multiMode ? $mixedId : [$mixedId];
		$missing = [];
		foreach ($ids as $id) {
			if ($id && !isset(self::$ownerLabelCache[$id])) {
				$missing[] = $id;
			}
		}
		if (!empty($missing)) {
			foreach ($missing as $userId) {
				self::getUserLabel($userId);
			}
			$diffIds = array_diff($missing, array_keys(self::$ownerLabelCache));
			if ($diffIds) {
				foreach ($diffIds as $groupId) {
					self::getGroupName($groupId);
				}
			}
		}
		$result = [];
		foreach ($ids as $id) {
			if (isset(self::$ownerLabelCache[$id])) {
				$result[$id] = self::$ownerLabelCache[$id];
			} else {
				$result[$id] = NULL;
			}
		}
		return $multiMode ? $result : array_shift($result);
	}

	public static function getGroupName($id)
	{
		if (isset(self::$groupLabelCache[$id])) {
			return self::$groupLabelCache[$id];
		}
		$label = false;
		$instance = new self();
		$groups = $instance->getGroups(false);
		if (isset($groups[$id])) {
			$label = $groups[$id];
			self::$groupLabelCache[$id] = $label;
			self::$ownerLabelCache[$id] = $label;
		}
		return $label;
	}

	public static function getUserLabel($id, $single = false)
	{
		if (isset(self::$userLabelCache[$id])) {
			return self::$userLabelCache[$id];
		}

		if (\AppConfig::performance('ENABLE_CACHING_USERS')) {
			$users = \includes\PrivilegeFile::getUser('id');
		} else {
			$instance = new self();
			if ($single) {
				$users = $instance->initUsers('Active', $id);
			} else {
				$users = $instance->initUsers();
			}
		}
		foreach ($users as $uid => &$user) {
			self::$userLabelCache[$uid] = $user['fullName'];
			self::$ownerLabelCache[$uid] = $user['fullName'];
		}
		return isset($users[$id]) ? $users[$id]['fullName'] : false;
	}

	protected static $typeCache = [];

	public static function getType($id)
	{
		if (isset(self::$typeCache[$id])) {
			return self::$typeCache[$id];
		}
		if (\AppConfig::performance('ENABLE_CACHING_USERS')) {
			$users = \includes\PrivilegeFile::getUser('id');
		} else {
			$instance = new self();
			$users = $instance->initUsers();
		}
		$result = isset($users[$id]) ? 'Users' : 'Groups';
		self::$typeCache[$id] = $result;
		return $result;
	}
}
