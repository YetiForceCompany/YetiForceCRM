<?php

namespace App;

/**
 * User basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class User
{
	protected static $currentUserId;
	protected static $currentUserRealId = false;
	protected static $currentUserCache = false;
	protected static $userModelCache = [];
	protected $privileges = [];

	/**
	 * Get current user Id.
	 *
	 * @return int
	 */
	public static function getCurrentUserId()
	{
		return static::$currentUserId;
	}

	/**
	 * Set current user Id.
	 *
	 * @param int $userId
	 */
	public static function setCurrentUserId($userId)
	{
		if (!self::isExists($userId, false)) {
			throw new \App\Exceptions\AppException('User not exists: ' . $userId);
		}
		static::$currentUserId = $userId;
		static::$currentUserCache = false;
	}

	/**
	 * Get real current user Id.
	 *
	 * @return int
	 */
	public static function getCurrentUserRealId()
	{
		if (static::$currentUserRealId) {
			return static::$currentUserRealId;
		}
		if (\App\Session::has('baseUserId') && \App\Session::get('baseUserId')) {
			$id = \App\Session::get('baseUserId');
		} else {
			$id = static::getCurrentUserId();
		}
		static::$currentUserRealId = $id;
		return $id;
	}

	/**
	 * Get current user model.
	 *
	 * @return \self
	 */
	public static function getCurrentUserModel()
	{
		if (static::$currentUserCache) {
			return static::$currentUserCache;
		}
		if (!static::$currentUserId) {
			static::$currentUserId = (int) \App\Session::get('authenticated_user_id');
		}
		return static::$currentUserCache = static::getUserModel(static::$currentUserId);
	}

	/**
	 * Get user model by id.
	 *
	 * @param int $userId
	 *
	 * @return \self
	 */
	public static function &getUserModel($userId)
	{
		if (isset(static::$userModelCache[$userId])) {
			return static::$userModelCache[$userId];
		}
		$userModel = new self();
		if ($userId) {
			$userModel->privileges = static::getPrivilegesFile($userId)['_privileges'] ?? [];
			static::$userModelCache[$userId] = $userModel;
		}
		return $userModel;
	}

	protected static $userPrivilegesCache = [];

	/**
	 * Get base privileges from file by id.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public static function getPrivilegesFile($userId): array
	{
		if (isset(static::$userPrivilegesCache[$userId])) {
			return self::$userPrivilegesCache[$userId];
		}
		if (!file_exists("user_privileges/user_privileges_{$userId}.php")) {
			return [];
		}
		$privileges = require "user_privileges/user_privileges_{$userId}.php";

		$valueMap = [];
		$valueMap['id'] = $userId;
		$valueMap['is_admin'] = (bool) $is_admin;
		$valueMap['user_info'] = $user_info;
		$valueMap['_privileges'] = $privileges;
		if (!$is_admin) {
			$valueMap['roleid'] = $current_user_roles;
			$valueMap['parent_role_seq'] = $current_user_parent_role_seq;
			$valueMap['profiles'] = $current_user_profiles;
			$valueMap['profile_global_permission'] = $profileGlobalPermission;
			$valueMap['profile_tabs_permission'] = $profileTabsPermission;
			$valueMap['profile_action_permission'] = $profileActionPermission;
			$valueMap['groups'] = $current_user_groups;
			$valueMap['subordinate_roles'] = $subordinate_roles;
			$valueMap['parent_roles'] = $parent_roles;
			$valueMap['subordinate_roles_users'] = $subordinate_roles_users;
			$sharingPrivileges = static::getSharingFile($userId);
			$valueMap['defaultOrgSharingPermission'] = $sharingPrivileges['defOrgShare'];
			$valueMap['related_module_share'] = $sharingPrivileges['relatedModuleShare'];
		}
		self::$userPrivilegesCache[$userId] = $valueMap;

		return $valueMap;
	}

	/**
	 * Clear user cache.
	 *
	 * @param bool|int $userId
	 */
	public static function clearCache($userId = false)
	{
		if ($userId) {
			unset(self::$userPrivilegesCache[$userId], self::$userSharingCache[$userId], static::$userModelCache[$userId]);
			if (static::$currentUserId === $userId) {
				static::$currentUserCache = false;
			}
		} else {
			self::$userPrivilegesCache = self::$userSharingCache = static::$userModelCache = [];
			static::$currentUserCache = false;
		}
	}

	protected static $userSharingCache = [];

	/**
	 * Get sharing privileges from file by id.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public static function getSharingFile($userId)
	{
		if (isset(self::$userSharingCache[$userId])) {
			return self::$userSharingCache[$userId];
		}
		if (!file_exists("user_privileges/sharing_privileges_{$userId}.php")) {
			return null;
		}
		$sharingPrivileges = require "user_privileges/sharing_privileges_{$userId}.php";
		self::$userSharingCache[$userId] = $sharingPrivileges;

		return $sharingPrivileges;
	}

	/**
	 * Get user id.
	 *
	 * @return int|null
	 */
	public function getId()
	{
		return $this->privileges['details']['record_id'] ?? null;
	}

	/**
	 * Get user id.
	 *
	 * @return int
	 */
	public function getName()
	{
		return $this->privileges['displayName'];
	}

	/**
	 * Get user detail.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getDetail($fieldName)
	{
		return $this->privileges['details'][$fieldName] ?? null;
	}

	/**
	 * Get user all details.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function getDetails()
	{
		return $this->privileges['details'] ?? null;
	}

	/**
	 * Get user profiles.
	 *
	 * @return array
	 */
	public function getProfiles()
	{
		return $this->privileges['profiles'] ?? null;
	}

	/**
	 * Get user groups.
	 *
	 * @return array
	 */
	public function getGroups()
	{
		return $this->privileges['groups'] ?? null;
	}

	/**
	 * Get user group names.
	 *
	 * @return string[]
	 */
	public function getGroupNames()
	{
		return array_filter(\App\Fields\Owner::getInstance('CustomView')->getGroups(false), fn ($key) => \in_array($key, $this->getGroups()), ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Get user role Id.
	 *
	 * @return string
	 */
	public function getRole(): string
	{
		return $this->privileges['details']['roleid'];
	}

	/**
	 * Get user role Id.
	 *
	 * @return string
	 */
	public function getRoleName(): string
	{
		return $this->privileges['roleName'];
	}

	/**
	 * Get user role instance.
	 *
	 * @return \Settings_Roles_Record_Model
	 */
	public function getRoleInstance()
	{
		if (!empty($this->privileges['roleInstance'])) {
			return $this->privileges['roleInstance'];
		}
		return $this->privileges['roleInstance'] = \Settings_Roles_Record_Model::getInstanceById($this->getRole());
	}

	/**
	 * Get user parent roles.
	 *
	 * @return array
	 */
	public function getParentRoles()
	{
		return $this->privileges['parent_roles'];
	}

	/**
	 * Get user parent roles seq.
	 *
	 * @return string
	 */
	public function getParentRolesSeq()
	{
		return $this->privileges['parent_role_seq'];
	}

	/**
	 * Function to check whether the user is an Admin user.
	 *
	 * @return bool true/false
	 */
	public function isAdmin()
	{
		return !empty($this->privileges['details']['is_admin']);
	}

	/**
	 * Function to check whether the user is an super user.
	 *
	 * @return bool
	 */
	public function isSuperUser(): bool
	{
		return !empty($this->privileges['details']['super_user']);
	}

	/**
	 * Get user parameters.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->privileges[$key];
	}

	/**
	 * Check for existence of key.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return \array_key_exists($key, $this->privileges);
	}

	/**
	 * Set user parameters.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public function set(string $key, $value)
	{
		$this->privileges[$key] = $value;
		return $this;
	}

	/**
	 * Function checks if user is active.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return 'Active' === ($this->privileges['details']['status'] ?? null);
	}

	/**
	 * Function checks if user exists.
	 *
	 * @param int  $id     - User ID
	 * @param bool $active
	 *
	 * @return bool
	 */
	public static function isExists(int $id, bool $active = true): bool
	{
		$cacheKey = $active ? 'UserIsExists' : 'UserIsExistsInactive';
		if (Cache::has($cacheKey, $id)) {
			return Cache::get($cacheKey, $id);
		}
		$isExists = false;
		if (\App\Config::performance('ENABLE_CACHING_USERS')) {
			$users = PrivilegeFile::getUser('id');
			if (($active && isset($users[$id]) && 'Active' == $users[$id]['status'] && !$users[$id]['deleted']) || (!$active && isset($users[$id]))) {
				$isExists = true;
			}
		} else {
			$isExistsQuery = (new \App\Db\Query())->from('vtiger_users')->where(['id' => $id]);
			if ($active) {
				$isExistsQuery->andWhere(['status' => 'Active', 'deleted' => 0]);
			}
			$isExists = $isExistsQuery->exists();
		}
		Cache::save($cacheKey, $id, $isExists);
		return $isExists;
	}

	/**
	 * Function to get the user if of the active admin user.
	 *
	 * @return int - Active Admin User ID
	 */
	public static function getActiveAdminId()
	{
		$key = '';
		$cacheName = 'ActiveAdminId';
		if (Cache::has($cacheName, $key)) {
			return Cache::get($cacheName, $key);
		}
		$adminId = 1;
		if (\App\Config::performance('ENABLE_CACHING_USERS')) {
			$users = PrivilegeFile::getUser('id');
			foreach ($users as $id => $user) {
				if ('Active' === $user['status'] && 'on' === $user['is_admin']) {
					$adminId = $id;
					break;
				}
			}
		} else {
			$adminId = (new Db\Query())->select(['id'])
				->from('vtiger_users')
				->where(['is_admin' => 'on', 'status' => 'Active'])
				->orderBy(['id' => SORT_ASC])
				->scalar();
		}
		Cache::save($cacheName, $key, $adminId, Cache::LONG);

		return $adminId;
	}

	/**
	 * Function gets user ID by name.
	 *
	 * @param string $name
	 *
	 * @return int
	 */
	public static function getUserIdByName($name): ?int
	{
		if (Cache::has('UserIdByName', $name)) {
			return Cache::get('UserIdByName', $name);
		}
		$userId = (new Db\Query())->select(['id'])->from('vtiger_users')->where(['user_name' => $name])->scalar();
		return Cache::save('UserIdByName', $name, false !== $userId ? $userId : null, Cache::LONG);
	}

	/**
	 * Function gets user ID by user full name.
	 *
	 * @param string $fullName
	 *
	 * @return int
	 */
	public static function getUserIdByFullName(string $fullName): int
	{
		$instance = \App\Fields\Owner::getInstance();
		$instance->showRoleName = false;
		$users = array_column($instance->initUsers(), 'id', 'fullName');
		return $users[$fullName] ?? 0;
	}

	/**
	 * Get user image details.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array|string[]
	 */
	public function getImage()
	{
		if (Cache::has('UserImageById', $this->getId())) {
			return Cache::get('UserImageById', $this->getId());
		}
		$image = Json::decode($this->getDetail('imagename'));
		if (empty($image) || !($imageData = \current($image))) {
			return [];
		}
		$imageData['path'] = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . $imageData['path'];
		if (file_exists($imageData['path'])) {
			$imageData['url'] = "file.php?module=Users&action=MultiImage&field=imagename&record={$this->getId()}&key={$imageData['key']}";
		} else {
			$imageData = [];
		}
		Cache::save('UserImageById', $this->getId(), $imageData);
		return $imageData;
	}

	/**
	 * Gets member structure.
	 *
	 * @return array
	 */
	public function getMemberStructure(): array
	{
		$member[] = \App\PrivilegeUtil::MEMBER_TYPE_USERS . ":{$this->getId()}";
		foreach ($this->getGroups() as $groupId) {
			$member[] = \App\PrivilegeUtil::MEMBER_TYPE_GROUPS . ":{$groupId}";
		}
		$member[] = \App\PrivilegeUtil::MEMBER_TYPE_ROLES . ":{$this->getRole()}";
		foreach (explode('::', $this->getParentRolesSeq()) as $role) {
			$member[] = \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES . ":{$role}";
		}
		return $member;
	}

	/**
	 * Get user image details by id.
	 *
	 * @param int $userId
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array|string[]
	 */
	public static function getImageById(int $userId)
	{
		if (Cache::has('UserImageById', $userId)) {
			return Cache::get('UserImageById', $userId);
		}
		$userModel = static::getUserModel($userId);
		if (empty($userModel)) {
			return [];
		}
		return $userModel->getImage();
	}

	/**
	 * Get number of users.
	 *
	 * @return int
	 */
	public static function getNumberOfUsers(): int
	{
		if (Cache::has('NumberOfUsers', '')) {
			return Cache::get('NumberOfUsers', '');
		}
		$count = (new Db\Query())->from('vtiger_users')->where(['status' => 'Active'])->andWhere(['<>', 'id', 1])->count();
		Cache::save('NumberOfUsers', '', $count, Cache::LONG);
		return $count;
	}

	/**
	 * Update users labels.
	 *
	 * @param int $fromUserId
	 *
	 * @return void
	 */
	public static function updateLabels(int $fromUserId = 0): void
	{
		$timeLimit = 180;
		$timeMax = $timeLimit + time();
		$query = (new \App\Db\Query())->select(['id'])->where(['>=', 'id', $fromUserId])->from('vtiger_users');
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (time() >= $timeMax) {
				(new \App\BatchMethod(['method' => __METHOD__, 'params' => [$row['id'], microtime()]]))->save();
				break;
			}
			if (self::isExists($row['id'], false)) {
				$userRecordModel = \Users_Record_Model::getInstanceById($row['id'], 'Users');
				$userRecordModel->updateLabel();
			}
		}
	}

	/**
	 * The function gets the all users label.
	 *
	 * @return bool|array
	 */
	public static function getAllLabels()
	{
		return (new \App\Db\Query())->from('u_#__users_labels')->select(['id', 'label'])->createCommand()->queryAllByGroup();
	}

	/**
	 * Check the previous password.
	 *
	 * @param int    $userId
	 * @param string $password
	 *
	 * @return bool
	 */
	public static function checkPreviousPassword(int $userId, string $password): bool
	{
		return (new \App\Db\Query())->from('l_#__userpass_history')->where(['user_id' => $userId, 'pass' => Encryption::createHash($password)])->exists(\App\Db::getInstance('log'));
	}
}
