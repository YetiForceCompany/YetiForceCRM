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
			if (\includes\Modules::isModuleActive($module) && Users_Privileges_Model::isPermitted($module)) {
				self::$modulesByLevels[$details['level']][$module] = $details;
			}
		}
	}

	public static function getModulesHierarchy()
	{
		self::init();
		return self::$modulesHierarchy;
	}

	public static function getModuleLevel($moduleName)
	{
		self::init();
		return isset(self::$modulesHierarchy[$moduleName]) ? self::$modulesHierarchy[$moduleName]['level'] : false;
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
		if (isset(self::$modulesByLevels[$level])) {
			foreach (self::$modulesByLevels[$level] as $module => &$details) {
				if (Users_Privileges_Model::isPermitted($module, $actionName)) {
					$modules[$module] = $details;
				}
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
		$return = false;
		switch (self::getModuleLevel($moduleName)) {
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
		switch (self::getModuleLevel($moduleName)) {
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
		$db = PearDatabase::getInstance();

		$fields = [];
		if ($type == 0 || $type == 1) {
			if (isset(self::$relatedField[$moduleName][1])) {
				$fields = self::$relatedField[$moduleName][1];
			} else {
				$query = 'SELECT vtiger_field.tabid,vtiger_field.columnname,vtiger_field.fieldname,vtiger_field.tablename,vtiger_tab.name FROM vtiger_fieldmodulerel INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.presence = ? && vtiger_fieldmodulerel.relmodule = ?';
				$result = $db->pquery($query, [0, $moduleName]);
				while ($row = $db->getRow($result)) {
					$fields[] = $row;
				}
				self::$relatedField[$moduleName][1] = $fields;
			}
		}
		if ($type == 0 || $type == 2) {
			if (isset(self::$relatedField[$moduleName][2])) {
				$fields = array_merge($fields, self::$relatedField[$moduleName][2]);
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
				self::$relatedField[$moduleName][1] = $fields1;
			}
		}
		if ($type == 0 || $type == 3) {
			if (isset(self::$relatedField[$moduleName][3])) {
				$fields = array_merge($fields, self::$relatedField[$moduleName][3]);
			} else {
				$uitype = Vtiger_ModulesHierarchy_Model::getUitypeByModule($moduleName);
				$result = $db->pquery('SELECT vtiger_field.tabid,vtiger_field.fieldname,vtiger_field.columnname,vtiger_field.tablename,vtiger_tab.name FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.presence = ? && vtiger_field.uitype = ?', [0, $uitype]);
				$fields2 = [];
				while ($row = $db->getRow($result)) {
					$fields2[] = $row;
				}
				$fields = array_merge($fields, $fields2);
				self::$relatedField[$moduleName][3] = $fields2;
			}
		}
		return $fields;
	}

	public static function getRelatedFieldByModule($moduleName, $type = 0)
	{
		$fields = Vtiger_ModulesHierarchy_Model::getRelatedField($moduleName, $type);
		$modules = self::getChildModules($moduleName);
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
		self::init();
		$modules = [];
		switch (self::getModuleLevel($moduleName)) {
			case 0:
				$modules = array_keys(self::getModulesByLevel(1));
				break;
			case 1:
				if ($levelMod = self::getModulesByLevel(2)) {
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
		$moduleName = vtlib\Functions::getCRMRecordType($record);
		$records = $recordsLevel1 = [];
		if (in_array(0, $hierarchy)) {
			$records[] = $record;
		}
		$fields = self::getRelatedFieldByModule($moduleName);
		foreach ($fields as &$field) {
			$recordsByField = self::getRelatedRecordsByField($record, $field);
			$recordsLevel1 = array_merge($recordsLevel1, $recordsByField);
		}
		$level = self::getModuleLevel($moduleName);
		if (!($level == 0 && !in_array(1, $hierarchy))) {
			$records = array_merge($records, $recordsLevel1);
		}
		if ($level == 0 && in_array(2, $hierarchy)) {
			foreach ($recordsLevel1 as $record) {
				$recordsByHierarchy = self::getRelatedRecords($record, $hierarchy);
				$records = array_merge($records, $recordsByHierarchy);
			}
		}
		return array_unique($records);
	}

	protected static function getRelatedRecordsByField($record, $field)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$queryGenerator = new QueryGenerator($field['name'], $currentUserModel);
		$queryGenerator->setFields(['id']);
		$queryGenerator->setCustomCondition([
			'glue' => 'AND',
			'tablename' => $field['tablename'],
			'column' => $field['columnname'],
			'operator' => '=',
			'value' => $record
		]);
		$query = $queryGenerator->getQuery();
		$result = $db->query($query);

		$ids = [];
		while (($id = $db->getSingleValue($result)) !== false) {
			$ids[] = $id;
		}
		return $ids;
	}
}
