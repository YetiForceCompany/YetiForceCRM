<?php

namespace App;

/**
 * User basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class User
{
	protected static $currentUserId;
	protected static $currentUserRealId = false;
	protected static $currentUserCache = false;
	protected static $userModelCache = [];
	protected $privileges;

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
			$privileges = static::getPrivilegesFile($userId);
			$privileges = $privileges['_privileges'];
			$userModel->privileges = $privileges;
			static::$userModelCache[$userId] = $userModel;
		}
		return $userModel;
	}

	protected static $userPrivilegesCache = false;

	/**
	 * Get base privileges from file by id.
	 *
	 * @param int $userId
	 *
	 * @return array
	 */
	public static function getPrivilegesFile($userId)
	{
		if (isset(static::$userPrivilegesCache[$userId])) {
			return self::$userPrivilegesCache[$userId];
		}
		if (!file_exists("user_privileges/user_privileges_{$userId}.php")) {
			return null;
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
	 * @param int|bool $userId
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
	 * @return int
	 */
	public function getId()
	{
		return $this->privileges['details']['record_id'];
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
		return $this->privileges['details'][$fieldName];
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
		return $this->privileges['details'];
	}

	/**
	 * Get user profiles.
	 *
	 * @return array
	 */
	public function getProfiles()
	{
		return $this->privileges['profiles'];
	}

	/**
	 * Get user groups.
	 *
	 * @return array
	 */
	public function getGroups()
	{
		return $this->privileges['groups'];
	}

	/**
	 * Get user group names.
	 *
	 * @return string[]
	 */
	public function getGroupNames()
	{
		return array_filter(\App\Fields\Owner::getInstance('CustomView')->getGroups(false), function ($key) {
			return \in_array($key, $this->getGroups());
		}, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Get user role Id.
	 *
	 * @return string
	 */
	public function getRole()
	{
		return $this->privileges['details']['roleid'];
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
	 * @return array
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
		return $this->privileges['details']['is_admin'];
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
	 * Function checks if user is active.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return $this->privileges['details']['status'] === 'Active';
	}

	/**
	 * Function checks if user exists.
	 *
	 * @param int $id - User ID
	 *
	 * @return bool
	 */
	public static function isExists($id)
	{
		if (Cache::has('UserIsExists', $id)) {
			return Cache::get('UserIsExists', $id);
		}
		$isExists = false;
		if (\AppConfig::performance('ENABLE_CACHING_USERS')) {
			$users = PrivilegeFile::getUser('id');
			if (isset($users[$id]) && !$users[$id]['deleted']) {
				$isExists = true;
			}
		} else {
			$isExists = (new \App\Db\Query())
				->from('vtiger_users')
				->where(['status' => 'Active', 'deleted' => 0, 'id' => $id])
				->exists();
		}
		Cache::save('UserIsExists', $id, $isExists);

		return $isExists;
	}

	/**
	 * Function to get the user if of the active admin user.
	 *
	 * @return int - Active Admin User ID
	 */
	public static function getActiveAdminId()
	{
		$key = 'id';
		if (Cache::has(__METHOD__, $key)) {
			return Cache::get(__METHOD__, $key);
		} else {
			$adminId = 1;
			if (\AppConfig::performance('ENABLE_CACHING_USERS')) {
				$users = PrivilegeFile::getUser('id');
				foreach ($users as $id => $user) {
					if ($user['status'] === 'Active' && $user['is_admin'] === 'on') {
						$adminId = $id;
						break;
					}
				}
			} else {
				$adminId = (new Db\Query())->select(['id'])
					->from('vtiger_users')
					->where(['is_admin' => 'on', 'status' => 'Active'])
					->orderBy(['id' => SORT_ASC])
					->limit(1)->scalar();
			}
			Cache::save(__METHOD__, $key, $adminId, Cache::LONG);

			return $adminId;
		}
	}

	/**
	 * Function gets user ID by name.
	 *
	 * @param string $name
	 *
	 * @return int
	 */
	public static function getUserIdByName($name)
	{
		if (Cache::has(__METHOD__, $name)) {
			return Cache::get(__METHOD__, $name);
		}
		$userId = (new Db\Query())->select(['id'])->from('vtiger_users')->where(['user_name' => $name])->limit(1)->scalar();
		Cache::save(__METHOD__, $name, $userId, Cache::LONG);

		return $userId;
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
		$imageData['path'] = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $imageData['path'];
		$imageData['url'] = "file.php?module=Users&action=MultiImage&field=imagename&record={$this->getId()}&key={$imageData['key']}";
		Cache::save('UserImageById', $this->getId(), $imageData);
		return $imageData;
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
}
