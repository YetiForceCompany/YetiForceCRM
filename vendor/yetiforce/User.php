<?php
namespace App;

/**
 * User basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class User
{

	protected static $currentUserId;
	protected static $currentUserRealId = false;
	protected static $currentUserCache = false;
	protected static $userModelCache = [];
	protected $privileges;

	public static function getCurrentUserId()
	{
		return static::$currentUserId;
	}

	public static function setCurrentUserId($userId)
	{
		static::$currentUserId = $userId;
	}

	public function getCurrentUserRealId()
	{
		if (static::$currentUserRealId) {
			return static::$currentUserRealId;
		}
		if (\Vtiger_Session::has('baseUserId') && \Vtiger_Session::get('baseUserId')) {
			$id = \Vtiger_Session::get('baseUserId');
		} else {
			$id = static::getCurrentUserId();
		}
		static::$currentUserRealId = $id;
		return $id;
	}

	/**
	 * 
	 * @return User
	 */
	public static function getCurrentUserModel()
	{
		if (static::$currentUserCache) {
			return static::$currentUserCache;
		}
		if (!static::$currentUserId) {
			static::$currentUserId = \Vtiger_Session::get('authenticated_user_id');
		}
		return static::$currentUserCache = static::getUserModel(static::$currentUserId);
	}

	public static function getUserModel($userId)
	{
		if (isset(static::$userModelCache[$userId])) {
			return static::$userModelCache[$userId];
		}
		$privileges = static::getPrivilegesFile($userId);
		$privileges = $privileges['_privileges'];

		$userModel = new self();
		$userModel->privileges = $privileges;
		static::$userModelCache[$userId] = $userModel;
		return $userModel;
	}

	protected static $userPrivilegesCache = false;

	public static function getPrivilegesFile($userId)
	{
		if (isset(static::$userPrivilegesCache[$userId])) {
			return self::$userPrivilegesCache[$userId];
		}
		if (!file_exists("user_privileges/user_privileges_$userId.php")) {
			return null;
		}
		$privileges = require("user_privileges/user_privileges_$userId.php");

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

	protected static $userSharingCache = [];

	public static function getSharingFile($userId)
	{
		if (isset(self::$userSharingCache[$userId])) {
			return self::$userSharingCache[$userId];
		}
		if (!file_exists("user_privileges/sharing_privileges_$userId.php")) {
			return null;
		}
		$sharingPrivileges = require("user_privileges/sharing_privileges_$userId.php");
		self::$userSharingCache[$userId] = $sharingPrivileges;
		return $sharingPrivileges;
	}

	public function getDetail($key)
	{
		return $this->privileges['details'][$key];
	}

	public function get($key)
	{
		return $this->privileges[$key];
	}

	protected static $userExistsCache = [];

	/**
	 * Function checks if user exists
	 * @param int $id - User ID
	 * @return boolean
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
					->where(['status' => 'Active'])
					->where(['deleted' => 0])
					->andWhere(['id' => $id])->exists();
		}
		Cache::save('UserIsExists', $id, $isExists);
		return $isExists;
	}
}
