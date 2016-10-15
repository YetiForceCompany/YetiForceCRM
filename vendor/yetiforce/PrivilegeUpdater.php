<?php namespace App;

/**
 * Global privileges basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PrivilegeUpdater
{

	private static $globalSearchPermissionsCache = [];

	/**
	 * Checking if user can search globally
	 * @param string $moduleName
	 * @param int $userId
	 * @return bool
	 */
	public static function checkGlobalSearchPermissions($moduleName, $userId = false)
	{
		if ($userId === false) {
			$user = \Users_Record_Model::getCurrentUserModel();
			$userId = $user->getId();
		}
		if (isset(static::$globalSearchPermissionsCache[$userId][$moduleName])) {
			$users = static::getGlobalSearchUsers();
			$return = false;
			if (isset($users[$userId]) && in_array($moduleName, $users[$userId])) {
				$return = true;
			}
			return static::$globalSearchPermissionsCache[$userId][$moduleName] = $return;
		}
		return static::$globalSearchPermissionsCache[$userId][$moduleName];
	}

	private static $globalSearchUsersCache = false;

	/**
	 * Loading a list of modules for users with permissions for global search
	 * @return array
	 */
	public static function getGlobalSearchUsers()
	{
		if (static::$globalSearchUsersCache === false) {
			static::$globalSearchUsersCache = [];
			$adb = \PearDatabase::getInstance();
			$query = 'SELECT `userid`,`searchunpriv` FROM `vtiger_user2role` LEFT JOIN `vtiger_role` ON vtiger_role.roleid = vtiger_user2role.roleid WHERE vtiger_role.`searchunpriv` <> \'\'';
			$result = $adb->query($query);
			while ($row = $adb->getRow($result)) {
				static::$globalSearchUsersCache[$row['userid']] = explode(',', $row['searchunpriv']);
			}
		}
		return static::$globalSearchUsersCache;
	}

	/**
	 * Updating permissions to records and global search
	 * @param int $record
	 * @param string $moduleName
	 */
	public static function update($record, $moduleName)
	{
		$searchUsers = $recordAccessUsers = '';
		$users = \includes\fields\Owner::getUsersIds();
		foreach ($users as &$userId) {
			if (Privilege::isPermitted($moduleName, 'DetailView', $record, $userId)) {
				$recordAccessUsers .= ',' . $userId;
				$searchUsers .= ',' . $userId;
			} elseif (static::checkGlobalSearchPermissions($moduleName, $userId)) {
				$searchUsers .= ',' . $userId;
			}
		}
		if (!empty($searchUsers)) {
			$searchUsers .= ',';
		}
		if (!empty($recordAccessUsers)) {
			$recordAccessUsers .= ',';
		}
		$db = \App\Db::getInstance();
		$db->createCommand()
			->update('u_#__crmentity_search_label', [
				'userid' => $searchUsers,
				], 'crmid = ' . $record)
			->execute();
		$db->createCommand()
			->update('vtiger_crmentity', [
				'users' => $recordAccessUsers,
				], 'crmid = ' . $record)
			->execute();
	}

	/**
	 * Updating permissions to global search
	 * @param int $record
	 * @param string $moduleName
	 */
	public static function updateSearch($record, $moduleName)
	{
		$searchUsers = '';
		$users = \includes\fields\Owner::getUsersIds();
		foreach ($users as &$userId) {
			if (static::checkGlobalSearchPermissions($moduleName, $userId) || Privilege::isPermitted($moduleName, 'DetailView', $record, $userId)) {
				$searchUsers .= ',' . $userId;
			}
		}
		if (!empty($searchUsers)) {
			$searchUsers .= ',';
		}
		\App\Db::getInstance()->createCommand()
			->update('u_#__crmentity_search_label', [
				'userid' => $searchUsers,
				], 'crmid = ' . $record)
			->execute();
	}

	/**
	 * Updating permissions to records
	 * @param int $record
	 * @param string $moduleName
	 */
	public static function updateRecordAccess($record, $moduleName)
	{
		$recordAccessUsers = '';
		$users = \includes\fields\Owner::getUsersIds();
		foreach ($users as &$userId) {
			if (Privilege::isPermitted($moduleName, 'DetailView', $record, $userId)) {
				$recordAccessUsers .= ',' . $userId;
			}
		}
		if (!empty($recordAccessUsers)) {
			$recordAccessUsers .= ',';
		}
		\App\Db::getInstance()->createCommand()
			->update('vtiger_crmentity', [
				'users' => $recordAccessUsers,
				], 'crmid = ' . $record)
			->execute();
	}

	/**
	 * Add to global permissions update queue.
	 * @param string $moduleName Module name
	 * @param int $record If type = 1 starting number if type = 0 record ID
	 * @param int $priority
	 * @param int $type
	 */
	public static function setUpdater($moduleName, $record = false, $priority = false, $type = 1)
	{
		$params = [
			'module' => $moduleName,
			'type' => $type,
		];
		if ($record) {
			$params['crmid'] = $record;
		}
		if ($priority) {
			$params['priority'] = $priority;
		}
		$adb = \PearDatabase::getInstance();
		$insert = $update = $row = false;
		$result = $adb->pquery('SELECT * FROM `s_yf_privileges_updater` WHERE module = ? && type = ?', [$moduleName, 1]);
		if ($adb->getRowCount($result) === 1) {
			$row = $adb->getRow($result);
			if ($record === false) {
				if ($row['crmid'] != 0) {
					$update = true;
					$params['crmid'] = 0;
				}
			} elseif ($record < $row['crmid']) {
				$insert = true;
			}
		} elseif ($record === false) {
			$insert = true;
		} else {
			$result = $adb->pquery('SELECT * FROM `s_yf_privileges_updater` WHERE module = ? && type = ? && crmid = ?', [$moduleName, 0, $record]);
			if ($adb->getRowCount($result) === 0) {
				$insert = true;
				$params['type'] = 0;
			}
		}
		if ($insert) {
			$adb->insert('s_yf_privileges_updater', $params);
		}
		if ($update) {
			$adb->update('s_yf_privileges_updater', $params, 'module = ? && type = ?', [$moduleName, $type]);
		}
	}

	/**
	 * Updating permissions to all modules
	 */
	public static function setAllUpdater()
	{
		$modules = \vtlib\Functions::getAllModules();
		foreach ($modules as &$module) {
			self::setUpdater($module['name']);
		}
		PrivilegeAdvanced::reloadCache();
	}
}
