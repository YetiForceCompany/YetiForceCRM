<?php
namespace App;

/**
 * Modules hierarchy basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ModuleHierarchy
{

	protected static $hierarchy;
	protected static $modulesByLevels = [];

	public static function init()
	{
		if (isset(static::$hierarchy)) {
			return true;
		}
		static::$hierarchy = require('user_privileges/moduleHierarchy.php');
		foreach (static::$hierarchy['modulesHierarchy'] as $module => $details) {
			if (Module::isModuleActive($module) && Privilege::isPermitted($module)) {
				static::$modulesByLevels[$details['level']][$module] = $details;
			}
		}
	}

	public static function getModulesHierarchy()
	{
		static::init();
		return static::$hierarchy['modulesHierarchy'];
	}

	public static function getModuleLevel($moduleName)
	{
		static::init();
		return isset(static::$hierarchy['modulesHierarchy'][$moduleName]) ? static::$hierarchy['modulesHierarchy'][$moduleName]['level'] : false;
	}

	public static function getModulesMap1M($moduleName)
	{
		static::init();
		return static::$hierarchy['modulesMap1M'][$moduleName];
	}

	public static function getModulesMapMMBase()
	{
		static::init();
		return static::$hierarchy['modulesMapMMBase'];
	}

	public static function getModulesMapMMCustom($moduleName)
	{
		static::init();
		return static::$hierarchy['modulesMapMMCustom'][$moduleName];
	}

	public static function getModulesByLevel($level = 0)
	{
		static::init();
		return static::$modulesByLevels[$level];
	}

	/**
	 * Get modules list by uitype field
	 * @param int $uitype
	 * @return array
	 */
	public static function getModulesByUitype($uitype)
	{
		switch ($uitype) {
			case 67: $level = 0;
				break;
			case 66: $level = 1;
				break;
			case 68: $level = 2;
				break;
		}
		return static::getModulesByLevel($level);
	}

	public static function accessModulesByLevel($level = 0, $actionName = 'EditView')
	{
		static::init();
		$modules = [];
		if (isset(static::$modulesByLevels[$level])) {
			foreach (static::$modulesByLevels[$level] as $module => &$details) {
				if (Privilege::isPermitted($module, $actionName)) {
					$modules[$module] = $details;
				}
			}
		}
		return $modules;
	}

	public static function accessModulesByParent($parent, $actionName = 'EditView')
	{
		static::init();
		$modules = [];
		foreach (static::$hierarchy['modulesHierarchy'] as $module => &$details) {
			if (Privilege::isPermitted($module, $actionName)) {
				$modules[$details['parentModule']][$module] = $details;
			}
		}
		return $modules[$parent];
	}

	public static function getMappingRelatedField($moduleName, $field = false)
	{
		$return = false;
		switch (static::getModuleLevel($moduleName)) {
			case 0: $return = 'link';
				break;
			case 1: $return = 'process';
				break;
			case 2: $return = 'subprocess';
				break;
		}
		return $return;
	}

	public static function getRelationFieldByHierarchy($moduleName, $field = false)
	{
		static::init();
		if ($field != false && isset(static::$hierarchy['modulesMapRelatedFields'][$moduleName][$field])) {
			return static::$hierarchy['modulesMapRelatedFields'][$moduleName][$field];
		}
		if (isset(static::$hierarchy['modulesMapRelatedFields'][$moduleName])) {
			return static::$hierarchy['modulesMapRelatedFields'][$moduleName];
		}
		return [];
	}

	public static function getUitypeByModule($moduleName)
	{
		switch (static::getModuleLevel($moduleName)) {
			case 0: $return = 67;
				break;
			case 1: $return = 66;
				break;
			case 2: $return = 68;
				break;
		}
		return $return;
	}

	public static function getChildModules($moduleName)
	{
		static::init();
		$modules = [];
		switch (static::getModuleLevel($moduleName)) {
			case 0:
				$modules = array_keys(static::getModulesByLevel(1));
				break;
			case 1:
				if ($levelMod = static::getModulesByLevel(2)) {
					foreach ($levelMod as $mod => &$details) {
						if ($moduleName == $details['parentModule']) {
							$modules[] = $mod;
						}
					}
				}
				break;
		}
		return $modules;
	}

	public static function getRelatedRecords($record, $hierarchy)
	{
		$moduleName = Record::getType($record);
		$records = $recordsLevel1 = [];
		if (in_array(0, $hierarchy)) {
			$records[] = $record;
		}
		$fields = Field::getReletedFieldForModule(false, $moduleName);
		$modules = static::getChildModules($moduleName);
		foreach ($fields as $field) {
			if (in_array($field['name'], $modules)) {
				$recordsByField = static::getRelatedRecordsByField($record, $field);
				$recordsLevel1 = array_merge($recordsLevel1, $recordsByField);
			}
		}
		$level = static::getModuleLevel($moduleName);
		if (!($level == 0 && !in_array(1, $hierarchy))) {
			$records = array_merge($records, $recordsLevel1);
		}
		if ($level == 0 && in_array(2, $hierarchy)) {
			foreach ($recordsLevel1 as $record) {
				$recordsByHierarchy = static::getRelatedRecords($record, $hierarchy);
				$records = array_merge($records, $recordsByHierarchy);
			}
		}
		return array_unique($records);
	}

	protected static function getRelatedRecordsByField($record, $field)
	{
		$queryGenerator = new QueryGenerator($field['name']);
		$queryGenerator->setFields(['id']);
		$queryGenerator->addNativeCondition([$field['tablename'] . '.' . $field['columnname'] => $record]);
		return $queryGenerator->createQuery()->column();
	}
}
