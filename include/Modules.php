<?php namespace includes;

/**
 * Record basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Modules
{

	protected static $moduleEntityCacheByName = [];
	protected static $moduleEntityCacheById = [];

	static public function getEntityInfo($mixed = false)
	{
		$entity = false;
		if ($mixed) {
			if (is_numeric($mixed))
				$entity = isset(self::$moduleEntityCacheById[$mixed]) ? self::$moduleEntityCacheById[$mixed] : false;
			else
				$entity = isset(self::$moduleEntityCacheByName[$mixed]) ? self::$moduleEntityCacheByName[$mixed] : false;
		}
		if (!$entity) {
			$adb = \PearDatabase::getInstance();
			$result = $adb->query('SELECT * from vtiger_entityname');
			while ($row = $adb->getRow($result)) {
				$row['fieldnameArr'] = explode(',', $row['fieldname']);
				$row['searchcolumnArr'] = explode(',', $row['searchcolumn']);
				self::$moduleEntityCacheByName[$row['modulename']] = $row;
				self::$moduleEntityCacheById[$row['tabid']] = $row;
			}
			if ($mixed) {
				if (is_numeric($mixed))
					return self::$moduleEntityCacheById[$mixed];
				else
					return self::$moduleEntityCacheByName[$mixed];
			}
		}
		return $entity;
	}

	static public function getAllEntityModuleInfo($sort = false)
	{
		if (empty(self::$moduleEntityCacheById)) {
			self::getEntityInfo();
		}
		$entity = [];
		if ($sort) {
			foreach (self::$moduleEntityCacheById as $tabid => $row) {
				$entity[$row['sequence']] = $row;
			}
			ksort($entity);
		} else {
			$entity = self::$moduleEntityCacheById;
		}
		return $entity;
	}

	protected static $isModuleActiveCache = [];

	static public function isModuleActive($moduleName)
	{
		if (isset(self::$isModuleActiveCache[$moduleName])) {
			return self::$isModuleActiveCache[$moduleName];
		}
		$moduleAlwaysActive = ['Administration', 'CustomView', 'Settings', 'Users', 'Migration',
			'Utilities', 'uploads', 'Import', 'System', 'com_vtiger_workflow', 'PickList'
		];
		if (in_array($moduleName, $moduleAlwaysActive)) {
			self::$isModuleActiveCache[$moduleName] = true;
			return true;
		}
		$tabPresence = self::getTabData('tabPresence');
		$isActive = $tabPresence[self::getModuleId($moduleName)] == 0 ? true : false;
		self::$isModuleActiveCache[$moduleName] = $isActive;
		return $isActive;
	}

	protected static $tabdataCache = false;

	static public function getTabData($type)
	{
		if (self::$tabdataCache === false) {
			self::$tabdataCache = require 'user_privileges/tabdata.php';
		}
		return isset(self::$tabdataCache[$type]) ? self::$tabdataCache[$type] : false;
	}

	public static function getModuleId($name)
	{
		$tabId = self::getTabData('tabId');
		return isset($tabId[$name]) ? $tabId[$name] : false;
	}

	public static function getModuleName($tabId)
	{
		return \vtlib\Functions::getModuleName($tabId);
	}
}
