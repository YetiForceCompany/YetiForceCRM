<?php
namespace App;

/**
 * Modules basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Module
{

	protected static $moduleEntityCacheById = [];

	static public function getEntityInfo($mixed = false)
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

	static public function getAllEntityModuleInfo($sort = false)
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

	static public function isModuleActive($moduleName)
	{
		if (isset(static::$isModuleActiveCache[$moduleName])) {
			return static::$isModuleActiveCache[$moduleName];
		}
		$moduleAlwaysActive = ['Administration', 'CustomView', 'Settings', 'Users', 'Migration',
			'Utilities', 'uploads', 'Import', 'System', 'com_vtiger_workflow', 'PickList'
		];
		if (in_array($moduleName, $moduleAlwaysActive)) {
			static::$isModuleActiveCache[$moduleName] = true;
			return true;
		}
		$tabPresence = static::getTabData('tabPresence');
		$isActive = $tabPresence[static::getModuleId($moduleName)] == 0 ? true : false;
		static::$isModuleActiveCache[$moduleName] = $isActive;
		return $isActive;
	}

	protected static $tabdataCache = false;

	static public function getTabData($type)
	{
		if (static::$tabdataCache === false) {
			static::$tabdataCache = require 'user_privileges/tabdata.php';
		}
		return isset(static::$tabdataCache[$type]) ? static::$tabdataCache[$type] : false;
	}

	public static function getModuleId($name)
	{
		$tabId = static::getTabData('tabId');
		return isset($tabId[$name]) ? $tabId[$name] : false;
	}

	public static function getModuleName($tabId)
	{
		return \vtlib\Functions::getModuleName($tabId);
	}

	/**
	 * Function get module name
	 * @param string $moduleName
	 * @return string
	 */
	public static function getTabName($moduleName)
	{
		return $moduleName === 'Events' ? 'Calendar' : $moduleName;
	}

	/**
	 * Function to get the list of module for which the user defined sharing rules can be defined
	 * @param array $eliminateModules
	 * @return array
	 */
	public static function getSharingModuleList($eliminateModules = false)
	{
		$modules = \vtlib\Functions::getAllModules(true, true, 0, false, 0);
		$sharingModules = [];
		foreach ($modules as $tabId => $row) {
			if (!$eliminateModules || !in_array($row['name'], $eliminateModules)) {
				$sharingModules[] = $row['name'];
			}
		}
		return $sharingModules;
	}

	/**
	 * Get sql for name in display format
	 * @param string $moduleName
	 * @return string
	 */
	public static function getSqlForNameInDisplayFormat($moduleName)
	{
		$entityFieldInfo = static::getEntityInfo($moduleName);
		$fieldsName = $entityFieldInfo['fieldnameArr'];
		if (count($fieldsName) > 1) {
			$sqlString = 'CONCAT(';
			foreach ($fieldsName as &$column) {
				$sqlString .= "{$entityFieldInfo['tablename']}.$column,' ',";
			}
			$formattedName = new \yii\db\Expression(rtrim($sqlString, ',\' \',') . ')');
		} else {
			$fieldsName = array_pop($fieldsName);
			$formattedName = "{$entityFieldInfo['tablename']}.$fieldsName";
		}
		return $formattedName;
	}
}
