<?php
/**
 * Relation file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Relation class.
 */
class Relation
{
	/**
	 * Get all relation for module.
	 *
	 * @param string      $moduleName
	 * @param bool|null   $onlyActive
	 * @param string|null $relatedModuleName
	 *
	 * @return array
	 */
	public static function getByModule(string $moduleName, ?bool $onlyActive = false, ?string $relatedModuleName = null): array
	{
		if (Cache::has('App\Relation::getByModule', $moduleName)) {
			$allRelations = Cache::get('App\Relation::getByModule', $moduleName);
		} else {
			$allRelations = [];
			$dataReader = (new Db\Query())->select(['vtiger_relatedlists.*', 'related_modulename' => 'vtiger_tab.name', 'related_module_presence' => 'vtiger_tab.presence'])
				->from('vtiger_relatedlists')->innerJoin('vtiger_tab', 'vtiger_relatedlists.related_tabid = vtiger_tab.tabid')
				->where(['vtiger_relatedlists.tabid' => Module::getModuleId($moduleName)])->orderBy(['sequence' => SORT_ASC])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$row['relation_id'] = (int) $row['relation_id'];
				$row['tabid'] = (int) $row['tabid'];
				$row['related_tabid'] = (int) $row['related_tabid'];
				$row['presence'] = (int) $row['presence'];
				$row['sequence'] = (int) $row['sequence'];
				$row['favorites'] = (int) $row['favorites'];
				$row['creator_detail'] = (int) $row['creator_detail'];
				$row['relation_comment'] = (int) $row['relation_comment'];
				$allRelations[$row['relation_id']] = $row;
			}
			Cache::save('App\Relation::getByModule', $moduleName, $allRelations, Cache::LONG);
		}
		if ($onlyActive) {
			foreach ($allRelations as $relationId => $relations) {
				if (0 !== $relations['related_module_presence']) {
					unset($allRelations[$relationId]);
				}
			}
		}
		if ($relatedModuleName) {
			foreach ($allRelations as $relationId => $relations) {
				if ($relatedModuleName !== $relations['related_modulename']) {
					unset($allRelations[$relationId]);
				}
			}
		}
		return $allRelations;
	}

	/**
	 * Get relation by id.
	 *
	 * @param int $relationId
	 *
	 * @return array
	 */
	public static function getById(int $relationId): array
	{
		if (Cache::has('App\Relation::getById', $relationId)) {
			return Cache::get('App\Relation::getById', $relationId);
		}
		$row = (new Db\Query())->select(['vtiger_relatedlists.*', 'related_modulename' => 'vtiger_tab.name', 'related_module_presence' => 'vtiger_tab.presence'])
			->from('vtiger_relatedlists')->innerJoin('vtiger_tab', 'vtiger_relatedlists.related_tabid = vtiger_tab.tabid')
			->where(['vtiger_relatedlists.relation_id' => $relationId])->one();
		if ($row) {
			$row['relation_id'] = (int) $row['relation_id'];
			$row['tabid'] = (int) $row['tabid'];
			$row['related_tabid'] = (int) $row['related_tabid'];
			$row['presence'] = (int) $row['presence'];
			$row['sequence'] = (int) $row['sequence'];
			$row['favorites'] = (int) $row['favorites'];
			$row['creator_detail'] = (int) $row['creator_detail'];
			$row['relation_comment'] = (int) $row['relation_comment'];
		} else {
			$row = [];
		}
		Cache::save('App\Relation::getById', $relationId, $row, Cache::LONG);
		return $row;
	}

	/**
	 * Get relation id by modules.
	 *
	 * @param string      $moduleName
	 * @param string      $relModuleName
	 * @param string|null $fieldName
	 *
	 * @return int|null
	 */
	public static function getRelationId(string $moduleName, string $relModuleName, ?string $fieldName = null): ?int
	{
		$relationId = null;
		foreach (self::getByModule($moduleName, true, $relModuleName) as $key => $relation) {
			if (isset($fieldName) && $fieldName === $relation['field_name']) {
				return $key;
			}
			$relationId = $relation['relation_id'];
		}
		return $relationId;
	}

	/**
	 * Function clear cache by module name.
	 *
	 * @param string $moduleName
	 * @param bool   $child
	 *
	 * @return void
	 */
	public static function clearCacheByModule(string $moduleName, bool $child = true): void
	{
		if ($child) {
			foreach (self::getByModule($moduleName) as $relation) {
				self::clearCacheById($relation['relation_id'], false);
			}
		}
		Cache::delete('App\Relation::getByModule', $moduleName);
		Cache::delete('HierarchyByRelation', '');
	}

	/**
	 * Function clear cache by module name.
	 *
	 * @param string $relationId
	 * @param bool   $parent
	 *
	 * @return void
	 */
	public static function clearCacheById(int $relationId, bool $parent = true): void
	{
		if ($parent && ($relation = self::getById($relationId)) && ($moduleName = \App\Module::getModuleName($relation['tabid']))) {
			self::clearCacheByModule($moduleName, false);
		}
		Cache::delete('App\Relation::getById', $relationId);
		Cache::delete('getFieldsFromRelation', $relationId);
		Cache::delete('HierarchyByRelation', '');
	}
}
