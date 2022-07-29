<?php
/**
 * Owner class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Fields;

class Owner
{
	/**
	 * Module name or false.
	 *
	 * @var bool|string
	 */
	protected $moduleName = false;
	protected $searchValue;
	protected $currentUser;
	/**
	 * Show role name.
	 *
	 * @var bool
	 */
	public $showRoleName = false;

	/**
	 * Function to get the instance.
	 *
	 * @param string $moduleName
	 * @param mixed  $currentUser
	 *
	 * @return \self
	 */
	public static function getInstance($moduleName = false, $currentUser = false)
	{
		if ($currentUser && $currentUser instanceof \Users) {
			$currentUser = \App\User::getUserModel($currentUser->id);
		} elseif (false === $currentUser) {
			$currentUser = \App\User::getCurrentUserModel();
		} elseif (is_numeric($currentUser)) {
			$currentUser = \App\User::getUserModel($currentUser);
		} elseif (\is_object($currentUser) && 'Users_Record_Model' === \get_class($currentUser)) {
			$currentUser = \App\User::getUserModel($currentUser->getId());
		}
		$cacheKey = $moduleName . $currentUser->getId();
		$instance = \Vtiger_Cache::get('App\Fields\Owner', $cacheKey);
		if (false === $instance) {
			$instance = new self();
			if ($moduleName) {
				$instance->moduleName = $moduleName;
			}
			$instance->showRoleName = \App\Config::module('Users', 'SHOW_ROLE_NAME');
			$instance->currentUser = $currentUser;
			\Vtiger_Cache::set('App\Fields\Owner', $cacheKey, $instance);
		}
		return $instance;
	}

	public function find($value)
	{
		$this->searchValue = $value;
	}

	/**
	 * Function to get all the accessible groups.
	 *
	 * @param mixed $private
	 * @param mixed $fieldType
	 * @param mixed $translate
	 *
	 * @return array
	 */
	public function getAccessibleGroups($private = '', $fieldType = false, $translate = false)
	{
		$cacheKey = $private . $this->moduleName . $fieldType . $this->currentUser->getRole();
		if (!\App\Cache::has('getAccessibleGroups', $cacheKey)) {
			$currentUserRoleModel = \Settings_Roles_Record_Model::getInstanceById($this->currentUser->getRole());
			if (!empty($fieldType) && (5 === (int) $currentUserRoleModel->get('sharedOwner' === $fieldType ? 'assignedmultiowner' : 'allowassignedrecordsto')) && 'Public' !== $private) {
				$accessibleGroups = $this->getAllocation('groups', $private, $fieldType);
			} else {
				$accessibleGroups = $this->getGroups(false, $private);
			}
			\App\Cache::save('getAccessibleGroups', $cacheKey, $accessibleGroups);
		} else {
			$accessibleGroups = \App\Cache::get('getAccessibleGroups', $cacheKey);
		}
		if ($translate) {
			foreach ($accessibleGroups as &$name) {
				$name = \App\Language::translate($name);
			}
		}
		if (!empty($this->searchValue)) {
			$this->searchValue = strtolower($this->searchValue);
			$accessibleGroups = array_filter($accessibleGroups, fn ($name) => strstr(strtolower($name), $this->searchValue));
		}
		return $accessibleGroups;
	}

	/**
	 * Function to get all the accessible users.
	 *
	 * @param string $private
	 * @param mixed  $fieldType
	 *
	 * @return array
	 */
	public function getAccessibleUsers($private = '', $fieldType = false)
	{
		$cacheKey = $private . $this->moduleName . $fieldType . $this->currentUser->getRole();
		if (!\App\Cache::has('getAccessibleUsers', $cacheKey)) {
			$currentUserRoleModel = \Settings_Roles_Record_Model::getInstanceById($this->currentUser->getRole());
			$assignTypeValue = (int) $currentUserRoleModel->get('sharedOwner' === $fieldType ? 'assignedmultiowner' : 'allowassignedrecordsto');
			if (1 === $assignTypeValue || 'Public' === $private) {
				$accessibleUser = $this->getUsers(false, 'Active', '', 'Public', true);
			} elseif (2 === $assignTypeValue) {
				$currentUserRoleModel = \Settings_Roles_Record_Model::getInstanceById($this->currentUser->getRole());
				$sameLevelRoles = array_keys($currentUserRoleModel->getSameLevelRoles());
				$childrenRoles = \App\PrivilegeUtil::getRoleSubordinates($this->currentUser->getRole());
				$roles = array_merge($sameLevelRoles, $childrenRoles);
				$accessibleUser = $this->getUsers(false, 'Active', '', '', false, array_unique($roles));
			} elseif (3 === $assignTypeValue) {
				$childrenRoles = \App\PrivilegeUtil::getRoleSubordinates($this->currentUser->getRole());
				$accessibleUser = $this->getUsers(false, 'Active', '', '', false, array_unique($childrenRoles));
				$accessibleUser[$this->currentUser->getId()] = $this->currentUser->getName();
			} elseif (!empty($fieldType) && 5 === $assignTypeValue) {
				$accessibleUser = $this->getAllocation('users', '', $fieldType);
			} else {
				$accessibleUser[$this->currentUser->getId()] = $this->currentUser->getName();
			}
			\App\Cache::save('getAccessibleUsers', $cacheKey, $accessibleUser);
		}
		return \App\Cache::get('getAccessibleUsers', $cacheKey);
	}

	/**
	 * Get accessible.
	 *
	 * @param string $private
	 * @param bool   $fieldType
	 * @param bool   $translate
	 *
	 * @return array
	 */
	public function getAccessible($private = '', $fieldType = false, $translate = false)
	{
		return [
			'users' => $this->getAccessibleUsers($private, $fieldType),
			'groups' => $this->getAccessibleGroups($private, $fieldType, $translate),
		];
	}

	/**
	 * Get allocation.
	 *
	 * @param string $mode
	 * @param string $private
	 * @param string $fieldType
	 *
	 * @return array
	 */
	public function getAllocation($mode, $private, $fieldType)
	{
		$moduleName = false;
		if ('Settings' !== \App\Request::_get('parent') && $this->moduleName) {
			$moduleName = $this->moduleName;
		}
		$result = [];
		$usersGroups = \Settings_RecordAllocation_Module_Model::getRecordAllocationByModule($fieldType, $moduleName);
		$usersGroups = $usersGroups[$this->currentUser->getId()] ?? [];
		if ('users' == $mode) {
			$users = $usersGroups['users'] ?? [];
			if (!empty($users)) {
				$result = $this->getUsers(false, 'Active', $users);
			}
		} else {
			$groups = $usersGroups['groups'] ?? [];
			if (!empty($groups)) {
				$groupsAll = $this->getGroups(false, $private);
				foreach ($groupsAll as $ID => $name) {
					if (\in_array($ID, $groups)) {
						$result[$ID] = $name;
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Function initiates users list.
	 *
	 * @param string $status
	 * @param mixed  $assignedUser
	 * @param string $private
	 * @param mixed  $roles
	 *
	 * @return array
	 */
	public function &initUsers($status = 'Active', $assignedUser = '', $private = '', $roles = false)
	{
		$cacheKeyMod = 'private' === $private ? $this->moduleName : '';
		$cacheKeyAss = \is_array($assignedUser) ? md5(json_encode($assignedUser)) : $assignedUser;
		$cacheKeyRole = \is_array($roles) ? md5(json_encode($roles)) : $roles;
		$cacheKey = $cacheKeyMod . '_' . $status . '|' . $cacheKeyAss . '_' . $private . '|' . $cacheKeyRole . '_' . (int) $this->showRoleName;
		if (\App\Cache::has('getUsers', $cacheKey)) {
			$tempResult = \App\Cache::get('getUsers', $cacheKey);
		} else {
			$entityData = \App\Module::getEntityInfo('Users');
			$query = $this->getQueryInitUsers($private, $status, $roles);
			if (!empty($assignedUser)) {
				$query->andWhere(['vtiger_users.id' => $assignedUser]);
			}
			$tempResult = [];
			$dataReader = $query->createCommand()->query();
			// Get the id and the name.
			while ($row = $dataReader->read()) {
				$fullName = '';
				foreach ($entityData['fieldnameArr'] as &$field) {
					$row[$field] = \App\Purifier::encodeHtml($row[$field]);
					$fullName .= ' ' . $row[$field];
				}
				if ($this->showRoleName && isset($row['rolename'])) {
					$roleName = \App\Language::translate($row['rolename'], '_Base', false, true);
					$fullName .= " ({$roleName})";
					$row['rolename'] = \App\Purifier::encodeHtml($row['rolename']);
				}
				$row['fullName'] = trim($fullName);
				$tempResult[$row['id']] = $row;
			}
			\App\Cache::save('getUsers', $cacheKey, $tempResult);
		}
		return $tempResult;
	}

	/**
	 * Function gets sql query.
	 *
	 * @param mixed $private
	 * @param mixed $status
	 * @param mixed $roles
	 *
	 * @return \App\Db\Query
	 */
	public function getQueryInitUsers($private = false, $status = false, $roles = false)
	{
		$entityData = \App\Module::getEntityInfo('Users');
		$selectFields = array_unique(array_merge($entityData['fieldnameArr'], ['id' => 'id', 'is_admin', 'cal_color', 'status']));
		// Including deleted vtiger_users for now.
		$where = [];
		if ('private' === $private) {
			$userPrivileges = \App\User::getPrivilegesFile($this->currentUser->getId());
			\App\Log::trace('Sharing is Private. Only the current user should be listed');
			$whereSection = [
				'or',
				['id' => $this->currentUser->getId()],
				['id' => (new \App\Db\Query())
					->select(['vtiger_user2role.userid'])
					->from('vtiger_user2role')
					->innerJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid')
					->where(['like', 'parentrole', $userPrivileges['_privileges']['parent_role_seq'] . '::%', false]),
				],
			];
			if ($this->moduleName) {
				$whereSection[] = [
					'id' => (new \App\Db\Query())
						->select(['vtiger_tmp_write_user_sharing_per.shareduserid'])
						->from('vtiger_tmp_write_user_sharing_per')
						->where(['vtiger_tmp_write_user_sharing_per.userid' => $this->currentUser->getId(), 'vtiger_tmp_write_user_sharing_per.tabid' => \App\Module::getModuleId($this->moduleName)]),
				];
			}
			$query = (new \App\Db\Query())->select($selectFields)->from('vtiger_users')->where($whereSection);
			if ($this->showRoleName) {
				$query->addSelect(['vtiger_role.rolename'])->innerJoin('vtiger_user2role', 'vtiger_user2role.userid = vtiger_users.id')
					->innerJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid');
			}
		} elseif (false !== $roles) {
			$query = (new \App\Db\Query())->select($selectFields)->from('vtiger_users')->innerJoin('vtiger_user2role', 'vtiger_users.id = vtiger_user2role.userid');
			$where[] = ['vtiger_user2role.roleid' => $roles];
			if ($this->showRoleName) {
				$query->addSelect(['rolename'])->innerJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid');
			}
		} else {
			\App\Log::trace('Sharing is Public. All vtiger_users should be listed');
			$query = new \App\Db\Query();
			$query->select($selectFields)->from('vtiger_users');
			if ($this->showRoleName) {
				$query->addSelect(['vtiger_role.rolename'])
					->innerJoin('vtiger_user2role', 'vtiger_user2role.userid = vtiger_users.id')
					->innerJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid');
			}
		}
		if (!empty($this->searchValue)) {
			$where[] = ['like', \App\Module::getSqlForNameInDisplayFormat('Users'), $this->searchValue];
		}
		if ($status) {
			$where[] = ['status' => $status];
		}
		if ($where) {
			$query->andWhere(array_merge(['and'], $where));
		}
		return $query;
	}

	/**
	 * Function returns the user key in user array.
	 *
	 * @param bool             $addBlank
	 * @param string           $status       User status
	 * @param string|array|int $assignedUser User id
	 * @param string           $private      Sharing type
	 * @param bool             $onlyAdmin    Show only admin users
	 * @param bool             $roles
	 *
	 * @return array
	 */
	public function getUsers($addBlank = false, $status = 'Active', $assignedUser = '', $private = '', $onlyAdmin = false, $roles = false)
	{
		\App\Log::trace("Entering getUsers($addBlank,$status,$private) method ...");

		$tempResult = $this->initUsers($status, $assignedUser, $private, $roles);
		if (!\is_array($tempResult)) {
			return [];
		}
		$users = [];
		if (true === $addBlank) {
			// Add in a blank row
			$users[''] = '';
		}
		$adminInList = \App\Config::performance('SHOW_ADMINISTRATORS_IN_USERS_LIST');
		$isAdmin = $this->currentUser->isAdmin();
		foreach ($tempResult as $key => $row) {
			if (!$onlyAdmin || $isAdmin || !(!$adminInList && 'on' == $row['is_admin'])) {
				$users[$key] = $row['fullName'];
			}
		}
		asort($users);
		\App\Log::trace('Exiting getUsers method ...');

		return $users;
	}

	/**
	 * Function to get groups.
	 *
	 * @param bool   $addBlank
	 * @param string $private
	 *
	 * @return array
	 */
	public function getGroups($addBlank = true, $private = '')
	{
		\App\Log::trace("Entering getGroups($addBlank,$private) method ...");
		$moduleName = '';
		if ('Settings' !== \App\Request::_get('parent') && $this->moduleName) {
			$moduleName = $this->moduleName;
			$tabId = \App\Module::getModuleId($moduleName);
		}
		$cacheKey = $addBlank . $private . $moduleName;
		if (\App\Cache::has('OwnerGroups', $cacheKey)) {
			return \App\Cache::get('OwnerGroups', $cacheKey);
		}
		// Including deleted vtiger_users for now.
		\App\Log::trace('Sharing is Public. All vtiger_users should be listed');
		$query = (new \App\Db\Query())->select(['groupid', 'groupname'])->from('vtiger_groups');
		if ($moduleName && 'CustomView' !== $moduleName) {
			$subQuery = (new \App\Db\Query())->select(['groupid'])->from('vtiger_group2modules')->where(['tabid' => $tabId]);
			$query->where(['groupid' => $subQuery]);
		}

		if ('private' === $private) {
			$query->andWhere(['groupid' => $this->currentUser->getId()]);
			if ($this->currentUser->getGroups()) {
				$query->orWhere(['vtiger_groups.groupid' => $this->currentUser->getGroups()]);
			}
			if ($moduleName) {
				$unionQuery = (new \App\Db\Query())->select(['sharedgroupid as groupid', 'vtiger_groups.groupname as groupname'])
					->from('vtiger_tmp_write_group_sharing_per')
					->innerJoin('vtiger_groups', 'vtiger_tmp_write_group_sharing_per.sharedgroupid = vtiger_groups.groupid')
					->where(['vtiger_tmp_write_group_sharing_per.userid' => $this->currentUser->getId()])
					->andWhere(['vtiger_tmp_write_group_sharing_per.tabid' => $tabId]);
				$query->union($unionQuery);
			}
		}
		$query->orderBy(['groupname' => SORT_ASC]);
		$dataReader = $query->createCommand()->query();
		$tempResult = [];
		if (true === $addBlank) {
			// Add in a blank row
			$tempResult[''] = '';
		}
		while ($row = $dataReader->read()) {
			$tempResult[$row['groupid']] = $row['groupname'];
		}
		\App\Cache::save('OwnerGroups', $cacheKey, $tempResult);
		\App\Log::trace('Exiting getGroups method ...');

		return $tempResult;
	}

	/**
	 * Function returns list of accessible users for a module.
	 *
	 * @return array
	 */
	public function getAccessibleGroupForModule()
	{
		$curentUserPrivileges = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($this->currentUser->isAdmin() || $curentUserPrivileges->hasGlobalWritePermission()) {
			$groups = $this->getAccessibleGroups('');
		} else {
			$sharingAccessModel = $this->moduleName ? \Settings_SharingAccess_Module_Model::getInstance($this->moduleName) : false;
			if ($sharingAccessModel && $sharingAccessModel->isPrivate()) {
				$groups = $this->getAccessibleGroups('private');
			} else {
				$groups = $this->getAccessibleGroups('');
			}
		}
		return $groups;
	}

	/**
	 * Function returns list of accessible users for a module.
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public function getAccessibleUsersForModule()
	{
		$curentUserPrivileges = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($this->currentUser->isAdmin() || $curentUserPrivileges->hasGlobalWritePermission()) {
			$users = $this->getAccessibleUsers('');
		} else {
			$sharingAccessModel = $this->moduleName ? \Settings_SharingAccess_Module_Model::getInstance($this->moduleName) : false;
			if ($sharingAccessModel && $sharingAccessModel->isPrivate()) {
				$users = $this->getAccessibleUsers('private');
			} else {
				$users = $this->getAccessibleUsers('');
			}
		}
		return $users;
	}

	/**
	 * Get users and group for module list.
	 *
	 * @param bool       $view
	 * @param bool|array $conditions
	 * @param string     $fieldName
	 *
	 * @return array
	 */
	public function getUsersAndGroupForModuleList($view = false, $conditions = false, $fieldName = 'assigned_user_id')
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName, $this->currentUser->getId());
		if ($view) {
			$queryGenerator->initForCustomViewById($view);
		}
		if ($conditions) {
			$queryGenerator->addNativeCondition($conditions['condition']);
			if (!empty($conditions['join'])) {
				foreach ($conditions['join'] as $join) {
					$queryGenerator->addJoin($join);
				}
			}
		}
		$users = $groups = [];
		$queryGenerator->clearFields();
		if (false !== strpos($fieldName, ':')) {
			$queryField = $queryGenerator->getQueryRelatedField($fieldName);
			$queryGenerator->setFields([])->setCustomColumn($queryField->getColumnName())->addRelatedJoin($queryField->getRelated());
		} else {
			$queryGenerator->setFields([$fieldName]);
		}
		$ids = $queryGenerator->createQuery()->distinct()->column();
		$adminInList = \App\Config::performance('SHOW_ADMINISTRATORS_IN_USERS_LIST');
		foreach ($ids as $id) {
			$userModel = \App\User::getUserModel($id);
			$name = $userModel->getName();
			if (!empty($name) && ($adminInList || (!$adminInList && !$userModel->isAdmin()))) {
				$users[$id] = $name;
				if ($this->showRoleName) {
					$roleName = \App\Language::translate($userModel->getRoleInstance()->getName());
					$users[$id] .= " ({$roleName})";
				}
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

	/**
	 * The function retrieves all users with active status.
	 *
	 * @param string $status
	 *
	 * @return string
	 */
	public static function getAllUsers($status = 'Active')
	{
		$instance = new self();

		return $instance->initUsers($status);
	}

	protected static $usersIdsCache = [];

	/**
	 * The function retrieves user ids with active status.
	 *
	 * @param string $status
	 *
	 * @return array
	 */
	public static function getUsersIds($status = 'Active')
	{
		if (!isset(self::$usersIdsCache[$status])) {
			$rows = [];
			if (\App\Config::performance('ENABLE_CACHING_USERS')) {
				$rows = \App\PrivilegeFile::getUser('id');
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
	protected static $groupIdCache = [];

	/**
	 * Function gets labels.
	 *
	 * @param array|int $mixedId
	 *
	 * @return array|int
	 */
	public static function getLabel($mixedId)
	{
		$multiMode = \is_array($mixedId);
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
				$result[$id] = null;
			}
		}
		return $multiMode ? $result : array_shift($result);
	}

	/**
	 * The function gets the group names.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
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
			self::$groupLabelCache[$id] = self::$ownerLabelCache[$id] = $label;
			self::$groupIdCache[$label] = $id;
		}
		return $label;
	}

	/**
	 * Function to get the group id for a given group groupname.
	 *
	 * @param string $name
	 *
	 * @return int
	 */
	public static function getGroupId($name)
	{
		if (isset(self::$groupIdCache[$name])) {
			return self::$groupIdCache[$name];
		}
		$id = false;
		$instance = new self();
		$groups = array_flip($instance->getGroups(false));
		if (isset($groups[$name])) {
			$id = self::$groupIdCache[$name] = $groups[$name];
		}
		return $id;
	}

	/**
	 * The function gets the user label.
	 *
	 * @param int  $id
	 * @param bool $single
	 *
	 * @return bool|string
	 */
	public static function getUserLabel($id)
	{
		if (isset(self::$userLabelCache[$id])) {
			return self::$userLabelCache[$id];
		}
		$userLabel = false;
		if (\App\Config::performance('ENABLE_CACHING_USERS')) {
			$users = \App\PrivilegeFile::getUser('id');
			foreach ($users as $uid => $user) {
				self::$userLabelCache[$uid] = $user['fullName'];
				self::$ownerLabelCache[$uid] = $user['fullName'];
			}
			$userLabel = isset($users[$id]) ? $users[$id]['fullName'] : false;
		} else {
			if ($users = \App\User::getAllLabels()) {
				foreach ($users as $uid => &$user) {
					self::$userLabelCache[$uid] = $user;
					self::$ownerLabelCache[$uid] = $user;
				}
				$userLabel = $users[$id] ?? false;
			}
		}
		return $userLabel ?? false;
	}

	/**
	 * Gets favorite owners.
	 *
	 * @param string $ownerFieldType
	 *
	 * @return array
	 */
	public function getFavorites(string $ownerFieldType): array
	{
		$userId = $this->currentUser->getId();
		$tabId = \App\Module::getModuleId($this->moduleName);
		$cacheName = "{$tabId}:{$userId}:{$ownerFieldType}";
		if (!\App\Cache::has('getFavoriteOwners', $cacheName)) {
			$tableName = 'sharedOwner' === $ownerFieldType ? 'u_#__favorite_shared_owners' : 'u_#__favorite_owners';
			\App\Cache::save('getFavoriteOwners', $cacheName, (new \App\Db\Query())->select(['ownerid', 'owner' => 'ownerid'])
				->from($tableName)
				->where(['tabid' => $tabId, 'userid' => $userId])->createCommand()->queryAllByGroup());
		}
		return \App\Cache::get('getFavoriteOwners', $cacheName);
	}

	/**
	 * Change favorite owner state.
	 *
	 * @param string $ownerFieldType
	 * @param int    $ownerId
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \yii\db\Exception
	 *
	 * @return bool
	 */
	public function changeFavorites(string $ownerFieldType, int $ownerId): bool
	{
		$userId = $this->currentUser->getId();
		$tabId = \App\Module::getModuleId($this->moduleName);
		$dbCommand = \App\Db::getInstance()->createCommand();

		switch (self::getType($ownerId)) {
			case 'Users':
				$ownerList = $this->getAccessibleUsers('', $ownerFieldType);
				break;
			case 'Groups':
				$ownerList = $this->getAccessibleGroups('', $ownerFieldType);
				break;
			default:
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!isset($ownerList[$ownerId])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$tableName = 'sharedOwner' === $ownerFieldType ? 'u_#__favorite_shared_owners' : 'u_#__favorite_owners';
		if (isset($this->getFavorites($ownerFieldType)[$ownerId])) {
			$result = $dbCommand->delete($tableName, ['tabid' => $tabId, 'userid' => $userId, 'ownerid' => $ownerId])->execute();
		} else {
			$result = $dbCommand->insert($tableName, ['tabid' => $tabId, 'userid' => $userId, 'ownerid' => $ownerId])->execute();
		}
		\App\Cache::delete('getFavoriteOwners', "{$tabId}:{$userId}:{$ownerFieldType}");
		return (bool) $result;
	}

	/**
	 * @var bool|string Owners color
	 */
	protected static $colorsCache = false;

	/**
	 * Get owner color.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public static function getColor($id)
	{
		if (!static::$colorsCache) {
			if (file_exists(ROOT_DIRECTORY . '/app_data/owners_colors.php')) {
				static::$colorsCache = require ROOT_DIRECTORY . '/app_data/owners_colors.php';
			} else {
				static::$colorsCache = [];
			}
		}
		if (isset(static::$colorsCache[$id])) {
			return static::$colorsCache[$id];
		}
		$hash = md5('color' . $id);

		return '#' . substr($hash, 0, 2) . substr($hash, 2, 2) . substr($hash, 4, 2);
	}

	protected static $typeCache = [];

	/**
	 * Function checks record type.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public static function getType($id)
	{
		if (isset(self::$typeCache[$id])) {
			return self::$typeCache[$id];
		}
		if (\App\Config::performance('ENABLE_CACHING_USERS')) {
			$users = \App\PrivilegeFile::getUser('id');
			$isExists = isset($users[$id]);
		} else {
			$isExists = !empty(self::getUserLabel($id));
		}
		$result = $isExists ? 'Users' : 'Groups';
		self::$typeCache[$id] = $result;

		return $result;
	}

	/**
	 * Transfer ownership records.
	 *
	 * @param int $oldId
	 * @param int $newId
	 */
	public static function transferOwnership($oldId, $newId)
	{
		$db = \App\Db::getInstance();
		//Updating the smcreatorid,smownerid, modifiedby, smcreatorid in vtiger_crmentity
		$db->createCommand()->update('vtiger_crmentity', ['smcreatorid' => $newId], ['smcreatorid' => $oldId, 'setype' => 'ModComments'])->execute();
		$db->createCommand()->update('vtiger_crmentity', ['smownerid' => $newId], ['smownerid' => $oldId, 'setype' => 'ModComments'])->execute();
		$db->createCommand()->update('vtiger_crmentity', ['modifiedby' => $newId], ['modifiedby' => $oldId])->execute();
		//updating the vtiger_import_maps
		$db->createCommand()->update('vtiger_import_maps', ['date_modified' => date('Y-m-d H:i:s'), 'assigned_user_id' => $newId], ['assigned_user_id' => $oldId])->execute();
		$db->createCommand()->delete('vtiger_users2group', ['userid' => $oldId])->execute();

		$dataReader = (new \App\Db\Query())->select(['tabid', 'fieldname', 'tablename', 'columnname'])
			->from('vtiger_field')
			->leftJoin('vtiger_fieldmodulerel', 'vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid')
			->where(['or', ['uitype' => [52, 53, 77, 101]], ['uitype' => 10, 'relmodule' => 'Users']])
			->createCommand()->query();
		$columnList = [];
		while ($row = $dataReader->read()) {
			$column = $row['tablename'] . '.' . $row['columnname'];
			if (!\in_array($column, $columnList)) {
				$columnList[] = $column;
				if ('smcreatorid' === $row['columnname'] || 'smownerid' === $row['columnname']) {
					$db->createCommand()->update($row['tablename'], [$row['columnname'] => $newId], ['and', [$row['columnname'] => $oldId], ['<>', 'setype', 'ModComments']])
						->execute();
				} else {
					$db->createCommand()->update($row['tablename'], [$row['columnname'] => $newId], [$row['columnname'] => $oldId])
						->execute();
				}
			}
		}
		self::transferOwnershipForWorkflow($oldId, $newId);
	}

	/**
	 * Transfer ownership workflow tasks.
	 *
	 * @param type $oldId
	 * @param type $newId
	 */
	private static function transferOwnershipForWorkflow($oldId, $newId)
	{
		$db = \App\Db::getInstance();
		$ownerName = static::getLabel($oldId);
		$newOwnerName = static::getLabel($newId);
		//update workflow tasks Assigned User from Deleted User to Transfer User

		$nameSearchValue = '"fieldname":"assigned_user_id","value":"' . $ownerName . '"';
		$idSearchValue = '"fieldname":"assigned_user_id","value":"' . $oldId . '"';
		$fieldSearchValue = 's:16:"assigned_user_id"';
		$dataReader = (new \App\Db\Query())->select(['task', 'task_id', 'workflow_id'])->from('com_vtiger_workflowtasks')
			->where(['or like', 'task', [$nameSearchValue, $idSearchValue, $fieldSearchValue]])
			->createCommand()->query();
		require_once 'modules/com_vtiger_workflow/VTTaskManager.php';
		while ($row = $dataReader->read()) {
			$task = $row['task'];
			$taskComponents = explode(':', $task);
			$classNameWithDoubleQuotes = $taskComponents[2];
			$className = str_replace('"', '', $classNameWithDoubleQuotes);
			require_once 'modules/com_vtiger_workflow/tasks/' . $className . '.php';
			$unserializeTask = unserialize($task);
			if (isset($unserializeTask->field_value_mapping)) {
				$fieldMapping = \App\Json::decode($unserializeTask->field_value_mapping);
				if (!empty($fieldMapping)) {
					foreach ($fieldMapping as $key => $condition) {
						if ('assigned_user_id' == $condition['fieldname']) {
							$value = $condition['value'];
							if (is_numeric($value) && $value == $oldId) {
								$condition['value'] = $newId;
							} elseif ($value == $ownerName) {
								$condition['value'] = $newOwnerName;
							}
						}
						$fieldMapping[$key] = $condition;
					}
					$updatedTask = \App\Json::encode($fieldMapping);
					$unserializeTask->field_value_mapping = $updatedTask;
					$serializeTask = serialize($unserializeTask);
					$db->createCommand()->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
				}
			} else {
				//For VTCreateTodoTask and VTCreateEventTask
				if (isset($unserializeTask->assigned_user_id)) {
					$value = $unserializeTask->assigned_user_id;
					if ($value == $oldId) {
						$unserializeTask->assigned_user_id = $newId;
					}
					$serializeTask = serialize($unserializeTask);
					$db->createCommand()->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
				}
			}
		}
	}
}
