<?php

namespace App;

/**
 * Modules basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Module
{
	protected static $moduleEntityCacheById = [];

	/**
	 * Cache for tabdata.php.
	 *
	 * @var array
	 */
	protected static $tabdataCache;

	/**
	 * Init tabdata from file.
	 */
	public static function init()
	{
		static::$tabdataCache = require \ROOT_DIRECTORY . '/user_privileges/tabdata.php';
		static::$tabdataCache['tabName'] = array_flip(static::$tabdataCache['tabId']);
	}

	/**
	 * Init tabdata form db.
	 */
	public static function initFromDb()
	{
		static::$tabdataCache = static::getModuleMeta();
		static::$tabdataCache['tabName'] = array_flip(static::$tabdataCache['tabId']);
	}

	public static function getEntityInfo($mixed = false)
	{
		$entity = false;
		if ($mixed) {
			if (is_numeric($mixed)) {
				if (Cache::has('ModuleEntityById', $mixed)) {
					return Cache::get('ModuleEntityById', $mixed);
				}
			} else {
				if (Cache::has('ModuleEntityByName', $mixed)) {
					return Cache::get('ModuleEntityByName', $mixed);
				}
			}
		}
		if (!$entity) {
			$dataReader = (new \App\Db\Query())->from('vtiger_entityname')
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$row['fieldnameArr'] = explode(',', $row['fieldname']);
				$row['searchcolumnArr'] = explode(',', $row['searchcolumn']);
				Cache::save('ModuleEntityByName', $row['modulename'], $row);
				Cache::save('ModuleEntityById', $row['tabid'], $row);
				static::$moduleEntityCacheById[$row['tabid']] = $row;
			}
			if ($mixed) {
				if (is_numeric($mixed)) {
					return Cache::get('ModuleEntityById', $mixed);
				} else {
					return Cache::get('ModuleEntityByName', $mixed);
				}
			}
		}
		return $entity;
	}

	public static function getAllEntityModuleInfo($sort = false)
	{
		if (empty(static::$moduleEntityCacheById)) {
			static::getEntityInfo();
		}
		$entity = [];
		if ($sort) {
			foreach (static::$moduleEntityCacheById as $row) {
				$entity[$row['sequence']] = $row;
			}
			ksort($entity);
		} else {
			$entity = static::$moduleEntityCacheById;
		}
		return $entity;
	}

	protected static $isModuleActiveCache = [];

	public static function isModuleActive($moduleName)
	{
		if (isset(static::$isModuleActiveCache[$moduleName])) {
			return static::$isModuleActiveCache[$moduleName];
		}
		if (in_array($moduleName, ['CustomView', 'Users', 'Import', 'com_vtiger_workflow', 'PickList'])) {
			static::$isModuleActiveCache[$moduleName] = true;

			return true;
		}
		$moduleId = static::getModuleId($moduleName);
		$isActive = (isset(static::$tabdataCache['tabPresence'][$moduleId]) && static::$tabdataCache['tabPresence'][$moduleId] == 0);
		static::$isModuleActiveCache[$moduleName] = $isActive;

		return $isActive;
	}

	/**
	 * Get module id by module name.
	 *
	 * @param string $moduleName
	 *
	 * @return int|bool
	 */
	public static function getModuleId($moduleName)
	{
		return static::$tabdataCache['tabId'][$moduleName] ?? false;
	}

	/**
	 * Get module nane by module id.
	 *
	 * @param int $tabId
	 *
	 * @return string|bool
	 */
	public static function getModuleName($tabId)
	{
		return static::$tabdataCache['tabName'][$tabId] ?? false;
	}

	/**
	 * Get module owner by module id.
	 *
	 * @param int $tabId
	 *
	 * @return int
	 */
	public static function getModuleOwner($tabId)
	{
		return static::$tabdataCache['tabOwnedby'][$tabId] ?? false;
	}

	/**
	 * Function to get the list of module for which the user defined sharing rules can be defined.
	 *
	 * @param array $eliminateModules
	 *
	 * @return array
	 */
	public static function getSharingModuleList($eliminateModules = false)
	{
		$modules = \vtlib\Functions::getAllModules(true, true, 0, false, 0);
		$sharingModules = [];
		foreach ($modules as $row) {
			if (!$eliminateModules || !in_array($row['name'], $eliminateModules)) {
				$sharingModules[] = $row['name'];
			}
		}
		return $sharingModules;
	}

	/**
	 * Get sql for name in display format.
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public static function getSqlForNameInDisplayFormat($moduleName)
	{
		$db = \App\Db::getInstance();
		$entityFieldInfo = static::getEntityInfo($moduleName);
		$fieldsName = $entityFieldInfo['fieldnameArr'];
		if (count($fieldsName) > 1) {
			$sqlString = 'CONCAT(';
			foreach ($fieldsName as &$column) {
				$sqlString .= "{$db->quoteTableName($entityFieldInfo['tablename'])}.{$db->quoteColumnName($column)},' ',";
			}
			$formattedName = new \yii\db\Expression(rtrim($sqlString, ',\' \',') . ')');
		} else {
			$fieldsName = array_pop($fieldsName);
			$formattedName = "{$db->quoteTableName($entityFieldInfo['tablename'])}.{$db->quoteColumnName($fieldsName)}";
		}
		return $formattedName;
	}

	/**
	 * Function to get a action id for a given action name.
	 *
	 * @param string $action
	 *
	 * @return int|null
	 */
	public static function getActionId($action)
	{
		if (empty($action)) {
			return null;
		}
		if (Cache::has('getActionId', $action)) {
			return Cache::get('getActionId', $action);
		}
		$actionIds = static::$tabdataCache['actionId'];
		if (isset($actionIds[$action])) {
			$actionId = $actionIds[$action];
		}
		if (empty($actionId)) {
			$actionId = (new Db\Query())->select(['actionid'])->from('vtiger_actionmapping')->where(['actionname' => $action])->scalar();
		}
		if (is_numeric($actionId)) {
			$actionId = (int) $actionId;
		}
		Cache::save('getActionId', $action, $actionId, Cache::LONG);
		return $actionId;
	}

	/**
	 * Get module meta data.
	 *
	 * @return array
	 */
	public static function getModuleMeta()
	{
		$tabNames = $tabPresence = $tabOwned = [];
		$allModules = \vtlib\Functions::getAllModules(false, true);
		foreach ($allModules as $moduleInfo) {
			$tabNames[$moduleInfo['name']] = $tabId = (int) $moduleInfo['tabid'];
			$tabPresence[$tabId] = $moduleInfo['presence'];
			$tabOwned[$tabId] = $moduleInfo['ownedby'];
		}
		//Constructing the actionname=>actionid array
		$actionAll = [];
		$dataReader = (new Db\Query())->from(['vtiger_actionmapping'])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$actionname = $row['actionname'];
			$actionAll[$actionname] = $actionid = (int) $row['actionid'];
			if ((int) $row['securitycheck'] === 0) {
				$actionSecure[$actionid] = $actionname;
			}
		}
		return [
			'tabId' => $tabNames,
			'tabPresence' => $tabPresence,
			'tabOwnedby' => $tabOwned,
			'actionId' => $actionAll,
			'actionName' => $actionSecure,
		];
	}

	/**
	 * Func    tion to create file about modules.
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public static function createModuleMetaFile()
	{
		Cache::delete('moduleTabs', 'all');
		$filename = 'user_privileges/tabdata.php';
		if (file_exists($filename)) {
			if (is_writable($filename)) {
				if (!$handle = fopen($filename, 'w+')) {
					throw new Exceptions\NoPermitted("Cannot open file ($filename)");
				}
				$moduleMeta = static::getModuleMeta();
				$newbuf = "<?php\n";
				$newbuf .= '$tab_seq_array=' . Utils::varExport($moduleMeta['tabPresence']) . ";\n";
				$newbuf .= 'return ' . Utils::varExport($moduleMeta) . ";\n";
				fwrite($handle, $newbuf);
				fclose($handle);
				Cache::resetFileCache($filename);
			} else {
				Log::error("The file $filename is not writable");
			}
		} else {
			Log::error("The file $filename does not exist");
		}
		static::init();
	}
}

Module::init();
