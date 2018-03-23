<?php

namespace App;

/**
 * Modules hierarchy basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		static::$hierarchy = require 'user_privileges/moduleHierarchy.php';
		foreach (static::$hierarchy['modulesHierarchy'] as $module => $details) {
			if (Module::isModuleActive($module) && Privilege::isPermitted($module)) {
				static::$modulesByLevels[$details['level']][$module] = $details;
			}
		}
	}

	public static function getModulesHierarchy()
	{
		return static::$hierarchy['modulesHierarchy'];
	}

	public static function getModuleLevel($moduleName)
	{
		return isset(static::$hierarchy['modulesHierarchy'][$moduleName]) ? static::$hierarchy['modulesHierarchy'][$moduleName]['level'] : false;
	}

	public static function getModulesMap1M($moduleName)
	{
		if (isset(static::$hierarchy['modulesMap1M'][$moduleName])) {
			return static::$hierarchy['modulesMap1M'][$moduleName];
		}

		return [];
	}

	public static function getModulesMapMMBase()
	{
		if (isset(static::$hierarchy['modulesMapMMBase'])) {
			return static::$hierarchy['modulesMapMMBase'];
		}

		return false;
	}

	public static function getModulesMapMMCustom($moduleName)
	{
		if (isset(static::$hierarchy['modulesMapMMCustom'][$moduleName])) {
			return static::$hierarchy['modulesMapMMCustom'][$moduleName];
		}

		return false;
	}

	public static function getModulesByLevel($level = 0)
	{
		if (isset(static::$modulesByLevels[$level])) {
			return static::$modulesByLevels[$level];
		}

		return [];
	}

	/**
	 * Get modules list by uitype field.
	 *
	 * @param int $uitype
	 *
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
			case 65: $level = 3;
				break;
		}

		return static::getModulesByLevel($level);
	}

	public static function accessModulesByLevel($level = 0, $actionName = 'EditView')
	{
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
		$modules = [];
		foreach (static::$hierarchy['modulesHierarchy'] as $module => &$details) {
			if (Privilege::isPermitted($module, $actionName)) {
				$modules[$details['parentModule']][$module] = $details;
			}
		}
		return $modules[$parent];
	}

	public static function getMappingRelatedField($moduleName)
	{
		$return = false;
		switch (static::getModuleLevel($moduleName)) {
			case 0: $return = 'link';
				break;
			case 1: $return = 'process';
				break;
			case 2: $return = 'subprocess';
				break;
			case 3: $return = 'linkextend';
				break;
		}
		return $return;
	}

	/**
	 * The function takes a hierarchy relationship.
	 *
	 * @param string $moduleName
	 * @param bool   $field
	 *
	 * @return array
	 */
	public static function getRelationFieldByHierarchy($moduleName, $field = false)
	{
		if ($field !== false && isset(static::$hierarchy['modulesMapRelatedFields'][$moduleName][$field])) {
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
			case 3: $return = 65;
				break;
		}

		return $return;
	}

	/**
	 * Get child modules.
	 *
	 * @param string $moduleName
	 * @param int[]  $hierarchy
	 *
	 * @return string[]
	 */
	public static function getChildModules($moduleName, $hierarchy = [1])
	{
		$modules = [];
		switch (static::getModuleLevel($moduleName)) {
			case 0:
				$is1Level = in_array(1, $hierarchy);
				$is3Level = in_array(3, $hierarchy);
				if ($is1Level && $is3Level) {
					$modules = array_keys(array_merge(static::getModulesByLevel(1), static::getModulesByLevel(3)));
				} elseif ($is1Level) {
					$modules = array_keys(static::getModulesByLevel(1));
				} elseif ($is3Level) {
					$modules = array_keys(static::getModulesByLevel(3));
				}
				break;
			case 1:
				if ($levelMod = static::getModulesByLevel(2)) {
					foreach ($levelMod as $mod => $details) {
						if ($moduleName === $details['parentModule']) {
							$modules[] = $mod;
						}
					}
				}
				break;
		}

		return $modules;
	}

	/**
	 * Get related records by hierarchy.
	 *
	 * @param int   $record
	 * @param array $hierarchy
	 *
	 * @return int[]
	 */
	public static function getRelatedRecords($record, $hierarchy)
	{
		$moduleName = Record::getType($record);
		$records = $recordsLevel1 = $recordsLevel2 = [];
		if (in_array(0, $hierarchy)) {
			$records[] = $record;
		}
		$modules = static::getChildModules($moduleName, $hierarchy);
		if ($modules) {
			$fields = Field::getRelatedFieldForModule(false, $moduleName);
			foreach ($fields as $field) {
				if (in_array($field['name'], $modules)) {
					$recordsByField = static::getRelatedRecordsByField($record, $field);
					$recordsLevel1 = array_merge($recordsLevel1, $recordsByField);
				}
			}
		}
		$level = static::getModuleLevel($moduleName);
		if (!($level == 0 && !in_array(1, $hierarchy))) {
			$records = array_merge($records, $recordsLevel1);
		}
		if ($level === 0) {
			if (in_array(2, $hierarchy)) {
				$modules = static::getChildModules($moduleName, [1]);
				if ($modules) {
					$fields = Field::getRelatedFieldForModule(false, $moduleName);
					foreach ($fields as $field) {
						if (in_array($field['name'], $modules)) {
							$recordsByField = static::getRelatedRecordsByField($record, $field);
							$recordsLevel2 = array_merge($recordsLevel2, $recordsByField);
						}
					}
				}
				foreach ($recordsLevel2 as $record) {
					$recordsByHierarchy = static::getRelatedRecords($record, $hierarchy);
					$records = array_merge($records, $recordsByHierarchy);
				}
			}
			if (in_array(3, $hierarchy)) {
				$records = array_merge($records, $recordsLevel1);
			}
		}

		return array_unique($records);
	}

	/**
	 * Get related records by field.
	 *
	 * @param int   $record
	 * @param array $field
	 *
	 * @return int[]
	 */
	protected static function getRelatedRecordsByField($record, $field)
	{
		$queryGenerator = new QueryGenerator($field['name']);
		$queryGenerator->setFields(['id']);
		$queryGenerator->addNativeCondition([$field['tablename'] . '.' . $field['columnname'] => $record]);

		return $queryGenerator->createQuery()->column();
	}
}

ModuleHierarchy::init();
