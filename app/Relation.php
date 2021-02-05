<?php
/**
 * Relation file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * Get all relation.
	 *
	 * @param int|null $moduleId
	 * @param array    $conditions ex. ['related_tabid' => 13, 'related_module_presence' => 0], ['presence' => 0]
	 *
	 * @return array
	 */
	public static function getAll(?int $moduleId = null, array $conditions = [])
	{
		if (Cache::has('App\Relation::getAll', '')) {
			$allRelations = Cache::get('App\Relation::getAll', '');
		} else {
			$allRelations = [];
			$dataReader = (new Db\Query())->select(['vtiger_relatedlists.*', 'related_modulename' => 'vtiger_tab.name', 'related_module_presence' => 'vtiger_tab.presence'])
				->from('vtiger_relatedlists')
				->innerJoin('vtiger_tab', 'vtiger_relatedlists.related_tabid = vtiger_tab.tabid')
				->orderBy(['tabid' => \SORT_ASC, 'sequence' => \SORT_ASC])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$allRelations[$row['tabid']][] = $row;
			}
			Cache::save('App\Relation::getAll', '', $allRelations, Cache::LONG);
		}
		if ($conditions) {
			foreach ($allRelations as $tabId => $relations) {
				foreach ($relations as $key => $relation) {
					$unset = false;
					foreach ($conditions as $cName => $cVal) {
						if (isset($relation[$cName]) && $relation[$cName] != $cVal) {
							$unset = true;
						}
					}
					if ($unset) {
						unset($allRelations[$tabId][$key]);
					}
				}
			}
		}
		if ($moduleId) {
			$allRelations = $allRelations[$moduleId] ?? [];
		}
		return $allRelations;
	}

	/**
	 * Get relation ID.
	 *
	 * @param string      $moduleName
	 * @param string      $relModuleName
	 * @param string|null $fieldName
	 *
	 * @return int|null
	 */
	public static function getRelationId(string $moduleName, string $relModuleName, ?string $fieldName = null): ?int
	{
		$relation = self::getAll(\App\Module::getModuleId($moduleName), ['related_tabid' => \App\Module::getModuleId($relModuleName), 'field_name' => $fieldName]);
		return current($relation)['relation_id'] ?? null;
	}
}
