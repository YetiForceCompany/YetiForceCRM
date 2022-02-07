<?php

namespace App;

/**
 * Modules hierarchy basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		static::$hierarchy = require ROOT_DIRECTORY . '/app_data/moduleHierarchy.php';
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

	public static function getModulesByLevel($level = null)
	{
		if (null === $level) {
			return static::$modulesByLevels;
		}
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
			case 67:
				$level = 0;
				break;
			case 66:
				$level = 1;
				break;
			case 68:
				$level = 2;
				break;
			case 64:
				$level = 3;
				break;
			case 65:
				$level = 4;
				break;
			default:
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
			if (Privilege::isPermitted($module, $actionName) && isset($details['parentModule'])) {
				$modules[$details['parentModule']][$module] = $details;
			}
		}
		return $modules[$parent];
	}

	public static function getMappingRelatedField($moduleName)
	{
		$return = false;
		switch ((string) static::getModuleLevel($moduleName)) {
			case '0':
				$return = 'link';
				break;
			case '1':
				$return = 'process';
				break;
			case '2':
				$return = 'subprocess';
				break;
			case '3':
				$return = 'subprocess_sl';
				break;
			case '4':
				$return = 'linkextend';
				break;
			default:
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
		if (false !== $field && isset(static::$hierarchy['modulesMapRelatedFields'][$moduleName][$field])) {
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
			case 0:
				$return = 67;
				break;
			case 1:
				$return = 66;
				break;
			case 2:
				$return = 68;
				break;
			case 3:
				$return = 64;
				break;
			case 4:
				$return = 65;
				break;
			default:
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
				$is1Level = \in_array(1, $hierarchy);
				$isLevel4 = \in_array(4, $hierarchy);
				if ($is1Level && $isLevel4) {
					$modules = array_keys(array_merge(static::getModulesByLevel(1), static::getModulesByLevel(4)));
				} elseif ($is1Level) {
					$modules = array_keys(static::getModulesByLevel(1));
				} elseif ($isLevel4) {
					$modules = array_keys(static::getModulesByLevel(4));
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
			case 2:
				if (\in_array(3, $hierarchy)) {
					$modules = array_keys(static::getModulesByLevel(3));
				}
				break;
			default:
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
		if (\in_array(0, $hierarchy)) {
			$records[] = $record;
		}
		$modules = static::getChildModules($moduleName, $hierarchy);
		if ($modules) {
			$fields = Field::getRelatedFieldForModule(false, $moduleName);
			foreach ($fields as $field) {
				if (\in_array($field['name'], $modules)) {
					$recordsByField = static::getRelatedRecordsByField($record, $field);
					$recordsLevel1 = array_merge($recordsLevel1, $recordsByField);
				}
			}
		}
		$level = static::getModuleLevel($moduleName);
		if (!(0 == $level && !\in_array(1, $hierarchy))) {
			$records = array_merge($records, $recordsLevel1);
		}
		if (0 === $level) {
			if (\in_array(2, $hierarchy)) {
				$modules = static::getChildModules($moduleName, [1]);
				if ($modules) {
					$fields = Field::getRelatedFieldForModule(false, $moduleName);
					foreach ($fields as $field) {
						if (\in_array($field['name'], $modules)) {
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
			if (\in_array(3, $hierarchy)) {
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

	/**
	 * Function to get array of queries. Quries are used to create union.
	 *
	 * @param int      $record
	 * @param string   $moduleName
	 * @param array    $hierarchy
	 * @param Db\Query $subQuery
	 *
	 * @return array
	 */
	private static function getQueriesForRelatedRecords(int $record, string $moduleName, array $hierarchy, Db\Query $subQuery = null): array
	{
		$modules = static::getChildModules($moduleName, $hierarchy);
		$queries = [];
		if ($modules) {
			$fields = Field::getRelatedFieldForModule(false, $moduleName);
			foreach ($fields as $field) {
				if (\in_array($field['name'], $modules)) {
					$queryGenerator = new QueryGenerator($field['name']);
					$queryGenerator->setFields(['id']);
					if ($subQuery) {
						$queryGenerator->addNativeCondition([$field['tablename'] . '.' . $field['columnname'] => $subQuery]);
					} else {
						$queryGenerator->addNativeCondition([$field['tablename'] . '.' . $field['columnname'] => $record]);
					}
					$tempQuery = $queryGenerator->createQuery();
					$queries[] = $tempQuery;
					$queries = array_merge($queries, static::getQueriesForRelatedRecords($record, $field['name'], $hierarchy, clone $tempQuery));
				}
			}
		}
		return $queries;
	}

	/**
	 * Get related query by hierarchy.
	 *
	 * @param int   $record
	 * @param array $hierarchy
	 *
	 * @return Db\Query|null
	 */
	public static function getQueryRelatedRecords(int $record, array $hierarchy): ?Db\Query
	{
		$moduleName = Record::getType($record);
		$queries = static::getQueriesForRelatedRecords($record, $moduleName, $hierarchy);
		if (0 === \count($queries)) {
			return null;
		}
		$subQuery = $queries[0];
		unset($queries[0]);
		foreach ($queries as $query) {
			$subQuery->union($query);
		}
		return $subQuery;
	}

	/**
	 * Get fields for list filter.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getFieldsForListFilter(string $moduleName): array
	{
		$fields = [];
		$moduleId = \App\Module::getModuleId($moduleName);
		foreach (static::getHierarchyByRelation() as $relations) {
			foreach ($relations as $relation) {
				if ($relation['related_tabid'] === $moduleId && !empty($relation['rel_field_name'])) {
					$fields[$relation['field_name']][\App\Module::getModuleName($relation['tabid'])] = [$relation['rel_field_name'] => \App\Module::getModuleName($relation['rel_tabid'])];
				}
			}
		}
		return $fields;
	}

	/**
	 * Get hierarchy info by relation.
	 *
	 * @param int|null $relationId
	 *
	 * @return array
	 */
	public static function getHierarchyByRelation(int $relationId = null): array
	{
		if (Cache::has('HierarchyByRelation', '')) {
			$data = Cache::get('HierarchyByRelation', '');
		} else {
			$data = [];
			$dataReader = (new \App\Db\Query())
				->select(['SR.tabid', 'SR.field_name', 'SR.related_tabid', 'rel_field_name' => 'RR.field_name', 'rel_tabid' => 'RR.tabid', 'a_yf_record_list_filter.*'])
				->from('a_yf_record_list_filter')
				->leftJoin(['SR' => 'vtiger_relatedlists'], 'SR.relation_id=a_yf_record_list_filter.relationid')
				->leftJoin(['RR' => 'vtiger_relatedlists'], 'RR.relation_id=a_yf_record_list_filter.rel_relationid')
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$data[$row['relationid']][] = $row;
			}
			Cache::save('HierarchyByRelation', '', $data, Cache::LONG);
		}
		if (null === $relationId) {
			return $data;
		}

		return $data[$relationId] ?? [];
	}
}

ModuleHierarchy::init();
