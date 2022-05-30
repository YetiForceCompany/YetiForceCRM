<?php
/**
 * Base calendar right panel file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Base calendar right panel class.
 */
class Vtiger_CalendarRightPanel_Model
{
	/**
	 * Get users.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getUsersList(string $moduleName): array
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$roleInstance = Settings_Roles_Record_Model::getInstanceById($currentUser->get('roleid'));

		switch ($roleInstance->get('clendarallorecords')) {
			case 3:
				if (App\Config::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST') && !\App\Config::module($moduleName, 'DISABLED_SHOW_OWNER_ONLY_IN_LIST', false)) {
					$usersAndGroup = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getUsersAndGroupForModuleList();
					$users = $usersAndGroup['users'];
				} else {
					$users = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers();
				}
				break;
			case 1:
			case 2:
			default:
				$users[$currentUser->getId()] = $currentUser->getName();
				break;
		}
		if (!empty($users) && $favoriteUsers = self::getFavoriteUsers($moduleName)) {
			uksort($users,
				fn ($a, $b) => (int) (!isset($favoriteUsers[$a]) && isset($favoriteUsers[$b])));
		}
		return $users;
	}

	/**
	 * Get groups.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getGroupsList(string $moduleName): array
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$roleInstance = Settings_Roles_Record_Model::getInstanceById($currentUser->get('roleid'));
		switch ($roleInstance->get('clendarallorecords')) {
			case 1:
				$groups = [];
				break;
			case 2:
				$groups = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups();
				break;
			case 3:
				if (App\Config::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST') && !\App\Config::module($moduleName, 'DISABLED_SHOW_OWNER_ONLY_IN_LIST', false)) {
					$usersAndGroup = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getUsersAndGroupForModuleList();
					$groups = $usersAndGroup['group'];
				} else {
					$groups = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups();
				}
				break;
			default:
				break;
		}
		return $groups;
	}

	/**
	 * Get calendar types.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getCalendarTypes(string $moduleName): array
	{
		return Vtiger_Calendar_Model::getInstance($moduleName)->getCalendarTypes();
	}

	/**
	 * Return user favorite users.
	 *
	 * @param string $moduleName
	 *
	 * @return int[]
	 */
	public static function getFavoriteUsers(string $moduleName): array
	{
		$userId = \App\User::getCurrentUserId();
		if (\App\Cache::has('FavoriteUsers', $userId)) {
			$users = \App\Cache::get('FavoriteUsers', $userId);
		} else {
			$users = (new \App\Db\Query())->select(['fav_id', 'id'])
				->from('u_#__users_pinned')
				->where(['user_id' => $userId, 'tabid' => \App\Module::getModuleId($moduleName)])
				->createCommand()
				->queryAllByGroup();
			\App\Cache::save('FavoriteUsers', $userId, $users, \App\Cache::LONG);
		}
		return $users;
	}
}
