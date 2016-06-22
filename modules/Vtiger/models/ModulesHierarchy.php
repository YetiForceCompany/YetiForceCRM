<?php

/**
 * Base Modules Hierarchy Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ModulesHierarchy_Model
{

	protected static $modulesHierarchy = [];
	protected static $modulesByLevels = [];
	protected static $modulesMapRelatedFields = [];
	protected static $modulesMap1M = [];
	protected static $modulesMapMMBase = [];
	protected static $modulesMapMMCustom = [];

	public static function init()
	{
		if (!empty(self::$modulesHierarchy)) {
			return true;
		}
		include('user_privileges/moduleHierarchy.php');
		self::$modulesHierarchy = $modulesHierarchy;
		self::$modulesMapRelatedFields = $modulesMapRelatedFields;
		self::$modulesMap1M = $modulesMap1M;
		self::$modulesMapMMBase = $modulesMapMMBase;
		self::$modulesMapMMCustom = $modulesMapMMCustom;
		foreach (self::$modulesHierarchy as $module => &$details) {
			if (vtlib_isModuleActive($module) && Users_Privileges_Model::isPermitted($module)) {
				self::$modulesByLevels[$details['level']][$module] = $details;
			}
		}
	}

	public static function getModulesHierarchy()
	{
		self::init();
		return self::$modulesHierarchy;
	}

	public static function getModulesMap1M($moduleName)
	{
		self::init();
		return self::$modulesMap1M[$moduleName];
	}

	public static function getModulesMapMMBase()
	{
		self::init();
		return self::$modulesMapMMBase;
	}

	public static function getModulesMapMMCustom($moduleName)
	{
		self::init();
		return self::$modulesMapMMCustom[$moduleName];
	}

	public static function getModulesByLevel($level = 0)
	{
		self::init();
		return self::$modulesByLevels[$level];
	}

	public static function accessModulesByLevel($level = 0, $actionName = 'EditView')
	{
		self::init();
		$modules = [];
		foreach (self::$modulesByLevels[$level] as $module => &$details) {
			if (Users_Privileges_Model::isPermitted($module, $actionName)) {
				$modules[$module] = $details;
			}
		}
		return $modules;
	}

	public static function accessModulesByParent($parent, $actionName = 'EditView')
	{
		self::init();
		$modules = [];
		foreach (self::$modulesHierarchy as $module => &$details) {
			if (Users_Privileges_Model::isPermitted($module, $actionName)) {
				$modules[$details['parentModule']][$module] = $details;
			}
		}
		return $modules[$parent];
	}

	public static function getMappingRelatedField($moduleName, $field = false)
	{
		self::init();
		$module = self::$modulesHierarchy[$moduleName];
		switch ($module['level']) {
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
		self::init();
		if ($field != false && isset(self::$modulesMapRelatedFields[$moduleName][$field])) {
			return self::$modulesMapRelatedFields[$moduleName][$field];
		}
		if (isset(self::$modulesMapRelatedFields[$moduleName])) {
			return self::$modulesMapRelatedFields[$moduleName];
		}
		return [];
	}

	public static function getUitypeByModule($moduleName)
	{
		self::init();
		$module = self::$modulesHierarchy[$moduleName];
		switch ($module['level']) {
			case 0: $return = 67;
				break;
			case 1: $return = 66;
				break;
			case 2: $return = 68;
				break;
		}
		return $return;
	}

	public static function getRelatedField($moduleName, $type = 0)
	{
		$db = PearDatabase::getInstance();

		$fields = [];
		if ($type == 0 || $type == 1) {
			$query = 'SELECT vtiger_field.tabid,vtiger_field.columnname,vtiger_field.tablename FROM vtiger_fieldmodulerel INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid WHERE vtiger_fieldmodulerel.relmodule = ?';
			$result = $db->pquery($query, [$moduleName]);
			while ($row = $db->getRow($result)) {
				$fields[] = $row;
			}
		}
		if ($type == 0 || $type == 2) {
			$query = 'SELECT vtiger_field.tabid,vtiger_field.columnname,vtiger_field.tablename FROM vtiger_field 
INNER JOIN vtiger_ws_fieldtype ON vtiger_ws_fieldtype.uitype = vtiger_field.uitype
INNER JOIN vtiger_ws_referencetype ON vtiger_ws_referencetype.fieldtypeid = vtiger_ws_fieldtype.fieldtypeid
WHERE vtiger_ws_referencetype.`type` = ?';
			$result = $db->pquery($query, [$moduleName]);
			while ($row = $db->getRow($result)) {
				$fields[] = $row;
			}
		}
		if ($type == 0 || $type == 2) {
			$uitype = Vtiger_ModulesHierarchy_Model::getUitypeByModule($moduleName);
			$result = $db->pquery('SELECT tabid,columnname,tablename FROM vtiger_field WHERE uitype = ?', [$uitype]);
			while ($row = $db->getRow($result)) {
				$fields[] = $row;
			}
		}
		return $fields;
	}
}
