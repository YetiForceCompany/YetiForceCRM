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
		static::$hierarchy = require ('user_privileges/moduleHierarchy.php');
		foreach (static::$hierarchy['modulesHierarchy'] as $module => &$details) {
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

	protected static $relatedField = [];

	public static function getRelatedField($moduleName, $type = 0)
	{
		$db = \PearDatabase::getInstance();

		$fields = [];
		if ($type == 0 || $type == 1) {
			if (isset(static::$relatedField[$moduleName][1])) {
				$fields = static::$relatedField[$moduleName][1];
			} else {
				$query = 'SELECT vtiger_field.tabid,vtiger_field.columnname,vtiger_field.fieldname,vtiger_field.tablename,vtiger_tab.name FROM vtiger_fieldmodulerel INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.presence = ? && vtiger_fieldmodulerel.relmodule = ?';
				$result = $db->pquery($query, [0, $moduleName]);
				while ($row = $db->getRow($result)) {
					$fields[] = $row;
				}
				static::$relatedField[$moduleName][1] = $fields;
			}
		}
		if ($type == 0 || $type == 2) {
			if (isset(static::$relatedField[$moduleName][2])) {
				$fields = array_merge($fields, static::$relatedField[$moduleName][2]);
			} else {
				$query = 'SELECT vtiger_field.tabid,vtiger_field.fieldname,vtiger_field.columnname,vtiger_field.tablename,vtiger_tab.name FROM vtiger_field 
INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid
INNER JOIN vtiger_ws_fieldtype ON vtiger_ws_fieldtype.uitype = vtiger_field.uitype
INNER JOIN vtiger_ws_referencetype ON vtiger_ws_referencetype.fieldtypeid = vtiger_ws_fieldtype.fieldtypeid
WHERE vtiger_tab.presence = ? && vtiger_ws_referencetype.`type` = ?';
				$result = $db->pquery($query, [0, $moduleName]);
				$fields1 = [];
				while ($row = $db->getRow($result)) {
					$fields1[] = $row;
				}
				$fields = array_merge($fields, $fields1);
				static::$relatedField[$moduleName][1] = $fields1;
			}
		}
		if ($type == 0 || $type == 3) {
			if (isset(static::$relatedField[$moduleName][3])) {
				$fields = array_merge($fields, static::$relatedField[$moduleName][3]);
			} else {
				$uitype = static::getUitypeByModule($moduleName);
				$result = $db->pquery('SELECT vtiger_field.tabid,vtiger_field.fieldname,vtiger_field.columnname,vtiger_field.tablename,vtiger_tab.name FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.presence = ? && vtiger_field.uitype = ?', [0, $uitype]);
				$fields2 = [];
				while ($row = $db->getRow($result)) {
					$fields2[] = $row;
				}
				$fields = array_merge($fields, $fields2);
				static::$relatedField[$moduleName][3] = $fields2;
			}
		}
		return $fields;
	}

	public static function getRelatedFieldByModule($moduleName, $type = 0)
	{
		$fields = static::getRelatedField($moduleName, $type);
		$modules = static::getChildModules($moduleName);
		$return = [];
		foreach ($fields as &$field) {
			if (in_array($field['name'], $modules)) {
				$return[] = $field;
			}
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
		$moduleName = \vtlib\Functions::getCRMRecordType($record);
		$records = $recordsLevel1 = [];
		if (in_array(0, $hierarchy)) {
			$records[] = $record;
		}
		$fields = static::getRelatedFieldByModule($moduleName);
		foreach ($fields as &$field) {
			$recordsByField = static::getRelatedRecordsByField($record, $field);
			$recordsLevel1 = array_merge($recordsLevel1, $recordsByField);
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
