<?php

namespace App;

\Vtiger_Loader::includeOnce('~/modules/com_vtiger_workflow/VTJsonCondition.php');

/**
 * Advanced privilege class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PrivilegeAdvanced
{
	protected static $cacheFile = 'user_privileges/advancedPermission.php';
	protected static $cache = false;
	public static $webservice = true;

	/**
	 * Update advanced permissions cache.
	 */
	public static function reloadCache()
	{
		$db = Db::getInstance('admin');
		$query = (new Db\Query())->from('a_#__adv_permission')->where(['status' => 0])->orderBy(['priority' => SORT_DESC]);
		$dataReader = $query->createCommand($db)->query();
		$cache = [];
		while ($row = $dataReader->read()) {
			$members = \App\Json::decode($row['members']);
			$users = [];
			if (!empty($members)) {
				foreach ($members as &$member) {
					$users = array_merge($users, PrivilegeUtil::getUserByMember($member));
				}
				$users = array_unique($users);
			}
			$cache[$row['tabid']][$row['id']] = [
				'action' => $row['action'],
				'conditions' => $row['conditions'],
				'members' => array_flip($users),
			];
		}
		$content = '<?php return ' . Utils::varExport($cache) . ';' . PHP_EOL;
		file_put_contents(static::$cacheFile, $content, LOCK_EX);
		\App\Cache::resetFileCache(static::$cacheFile);
	}

	/**
	 * Load advanced permission rules for specific module.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function get($moduleName)
	{
		if (false === static::$cache) {
			static::$cache = require static::$cacheFile;
		}
		$tabid = Module::getModuleId($moduleName);

		return static::$cache[$tabid] ?? false;
	}

	/**
	 * Check advanced permissions.
	 *
	 * @param int    $record
	 * @param string $moduleName
	 * @param mixed  $userId
	 *
	 * @return bool|int
	 */
	public static function checkPermissions($record, $moduleName, $userId)
	{
		$privileges = static::get($moduleName);
		if (false === $privileges) {
			return false;
		}
		Log::trace("Check advanced permissions: $record,$moduleName,$userId");
		foreach ($privileges as $id => &$privilege) {
			if (!isset($privilege['members'][$userId])) {
				continue;
			}
			static::$webservice = false;
			$recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$test = (new \VTJsonCondition())->evaluate($privilege['conditions'], $recordModel);
			static::$webservice = true;
			if ($test) {
				Log::trace("Check advanced permissions test OK,action: {$privilege['action']},id: $id");

				return 0 === $privilege['action'] ? 1 : 0;
			}
			Log::trace("Check advanced permissions test FALSE , id: $id");
		}
		return false;
	}
}
