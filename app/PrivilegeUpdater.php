<?php

namespace App;

/**
 * Global privileges basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PrivilegeUpdater
{
	private static $globalSearchPermissionsCache = [];

	/**
	 * Checking if user can search globally.
	 *
	 * @param string $moduleName
	 * @param int    $userId
	 *
	 * @return bool
	 */
	public static function checkGlobalSearchPermissions($moduleName, $userId = false)
	{
		if (!$userId) {
			$userId = User::getCurrentUserId();
		}
		if (!isset(static::$globalSearchPermissionsCache[$userId][$moduleName])) {
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
	 * Loading a list of modules for users with permissions for global search.
	 *
	 * @return array
	 */
	public static function getGlobalSearchUsers()
	{
		if (!static::$globalSearchUsersCache) {
			static::$globalSearchUsersCache = [];
			$dataReader = (new Db\Query())->select(['userid', 'searchunpriv'])->from('vtiger_user2role')
				->leftJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid')
				->where(['<>', 'vtiger_role.searchunpriv', ''])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				static::$globalSearchUsersCache[$row['userid']] = explode(',', $row['searchunpriv']);
			}
		}
		return static::$globalSearchUsersCache;
	}

	/**
	 * Updating permissions to records and global search.
	 *
	 * @param int    $record
	 * @param string $moduleName
	 */
	public static function update($record, $moduleName)
	{
		$searchUsers = $recordAccessUsers = '';
		$users = \App\Fields\Owner::getUsersIds();
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
	 * Updating permissions to global search.
	 *
	 * @param int    $record
	 * @param string $moduleName
	 */
	public static function updateSearch($record, $moduleName)
	{
		$searchUsers = '';
		$users = \App\Fields\Owner::getUsersIds();
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
	 * Updating permissions to records.
	 *
	 * @param int    $record
	 * @param string $moduleName
	 */
	public static function updateRecordAccess($record, $moduleName)
	{
		$recordAccessUsers = '';
		$users = \App\Fields\Owner::getUsersIds();
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
	 *
	 * @param string $moduleName Module name
	 * @param int    $record     If type = 1 starting number if type = 0 record ID
	 * @param int    $priority
	 * @param int    $type
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
		$insert = $update = $row = false;
		$query = new \App\Db\Query();
		$row = $query->from('s_#__privileges_updater')->where(['module' => $moduleName, 'type' => 1])->limit(1)->one();
		if ($row) {
			if ($record === false) {
				if ($row['crmid'] != 0) {
					$update = true;
					$params['crmid'] = 0;
				}
			} elseif ($record < $row['crmid']) {
				$row = $query->from('s_#__privileges_updater')->where(['module' => $moduleName, 'type' => 0, 'crmid' => $record])->limit(1)->one();
				if ($row === false) {
					$insert = true;
				}
			}
		} elseif ($record === false) {
			$insert = true;
		} else {
			$row = $query->from('s_#__privileges_updater')->where(['module' => $moduleName, 'type' => 0, 'crmid' => $record])->limit(1)->one();
			if ($row === false) {
				$insert = true;
				$params['type'] = 0;
			}
		}
		$db = \App\Db::getInstance('admin');
		if ($insert) {
			$db->createCommand()->insert('s_#__privileges_updater', $params)->execute();
		}
		if ($update) {
			$db->createCommand()->update('s_#__privileges_updater', $params, ['module' => $moduleName, 'type' => $type])->execute();
		}
	}

	/**
	 * Updating permissions to all modules.
	 */
	public static function setAllUpdater()
	{
		$modules = \vtlib\Functions::getAllModules();
		foreach ($modules as $module) {
			static::setUpdater($module['name']);
		}
		PrivilegeAdvanced::reloadCache();
		if (\AppConfig::module('ModTracker', 'WATCHDOG')) {
			\Vtiger_Watchdog_Model::reloadCache();
		}
		\App\Cache::clear();
	}

	/**
	 * Update permissions while saving record.
	 *
	 * @param \Vtiger_Record_Model $record
	 */
	public static function updateOnRecordSave(\Vtiger_Record_Model $record)
	{
		if (!\AppConfig::security('CACHING_PERMISSION_TO_RECORD')) {
			return false;
		}
		static::setUpdater($record->getModuleName(), $record->getId(), 6, 0);
	}
}
