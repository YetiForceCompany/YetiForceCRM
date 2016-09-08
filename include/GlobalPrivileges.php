<?php namespace includes;

/**
 * Global privileges basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class GlobalPrivileges
{

	protected static $globalSearchCache = [];

	public static function globalSearchByModule($moduleName, $userId = false)
	{
		if (!$userId) {
			$user = \Users_Record_Model::getCurrentUserModel();
			$userId = $user->getId();
		}
		$users = self::getGlobalSearchUsers();
		if (isset($users[$userId]) && in_array($moduleName, $users[$userId])) {
			return true;
		}
		return false;
	}

	public static function globalSearchById($record, $moduleName, $userId = false)
	{
		if (self::globalSearchByModule($moduleName, $userId)) {
			return true;
		}
		return Privileges::isPermitted($moduleName, 'DetailView', $record, $userId);
	}

	public static function updateGlobalSearch($record, $moduleName)
	{
		$adb = \PearDatabase::getInstance();
		$glabalPrivileges = '';
		$users = \includes\fields\Owner::getUsersIds();
		foreach ($users as &$userId) {
			if (self::globalSearchById($record, $moduleName, $userId)) {
				$glabalPrivileges .= ',' . $userId; //sprintf("%'.05d", $userId)
			}
		}
		if (!empty($glabalPrivileges)) {
			$glabalPrivileges .= ',';
		}
		$adb->update('u_yf_crmentity_search_label', ['userid' => $glabalPrivileges], 'crmid = ?', [$record]);
	}

	protected static $globalSearchUsersCache = false;

	public static function getGlobalSearchUsers()
	{
		if (self::$globalSearchUsersCache === false) {
			self::$globalSearchUsersCache = [];
			$adb = \PearDatabase::getInstance();
			$query = 'SELECT `userid`,`searchunpriv` FROM `vtiger_user2role` LEFT JOIN `vtiger_role` ON vtiger_role.roleid = vtiger_user2role.roleid WHERE vtiger_role.`searchunpriv` <> \'\'';
			$result = $adb->query($query);
			while ($row = $adb->getRow($result)) {
				self::$globalSearchUsersCache[$row['userid']] = explode(',', $row['searchunpriv']);
			}
		}
		return self::$globalSearchUsersCache;
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
		if ($adb->getRowCount($result) == 1) {
			$row = $adb->getRow($result);
			if ($record == false) {
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
			if ($adb->getRowCount($result) == 0) {
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

	public static function setAllUpdater()
	{
		$modules = \vtlib\Functions::getAllModules();
		foreach ($modules as &$module) {
			self::setUpdater($module['name']);
		}
	}
}
