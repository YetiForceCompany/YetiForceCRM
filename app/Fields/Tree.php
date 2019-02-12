<?php
/**
 * Tree.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * Tree class.
 */
class Tree
{
	/**
	 * Get tree values by ID.
	 *
	 * @param int $templateId
	 *
	 * @return array[]
	 */
	public static function getValuesById($templateId)
	{
		if (\App\Cache::has('TreeValuesById', $templateId)) {
			return \App\Cache::get('TreeValuesById', $templateId);
		}
		$rows = (new \App\Db\Query())
			->from('vtiger_trees_templates_data')
			->where(['templateid' => $templateId])->indexBy('tree')->all();
		\App\Cache::save('TreeValuesById', $templateId, $rows, \App\Cache::MEDIUM);

		return $rows;
	}

	/**
	 * Get tree values by tree ID.
	 *
	 * @param int    $templateId
	 * @param string $tree
	 *
	 * @return array[]
	 */
	public static function getValueByTreeId($templateId, $tree)
	{
		$rows = static::getValuesById($templateId);

		return $rows[$tree];
	}

	/**
	 * Get picklist values.
	 *
	 * @param int    $templateId
	 * @param string $moduleName
	 *
	 * @return string[]
	 */
	public static function getPicklistValue($templateId, $moduleName)
	{
		$values = [];
		$dataTree = self::getValuesById((int) $templateId);
		foreach ($dataTree as $row) {
			$tree = $row['tree'];
			$parent = '';
			$parentName = '';
			if ($row['depth'] > 0) {
				$parentTrre = $row['parentTree'];
				$cut = strlen('::' . $tree);
				$parentTrre = substr($parentTrre, 0, -$cut);
				$pieces = explode('::', $parentTrre);
				$parent = end($pieces);
				$parentName = $dataTree[$parent]['name'];
				$parentName = '(' . \App\Language::translate($parentName, $moduleName) . ') ';
			}
			$values[$row['tree']] = $parentName . \App\Language::translate($row['name'], $moduleName);
		}
		return $values;
	}

	/**
	 * Get picklist value with graphics.
	 *
	 * @param int    $templateId
	 * @param string $moduleName
	 * @param string $treeId
	 *
	 * @return string[]
	 */
	public static function getPicklistValueImage($templateId, $moduleName, $treeId)
	{
		$value = ['name' => ''];
		$rows = self::getValuesById((int) $templateId);
		if (empty($rows[$treeId])) {
			return $value;
		}
		$row = $rows[$treeId];
		$parent = '';
		$parentName = '';
		if ($row['depth'] > 0) {
			$parentTrre = $row['parentTree'];
			$cut = strlen('::' . $treeId);
			$parentTrre = substr($parentTrre, 0, -$cut);
			$pieces = explode('::', $parentTrre);
			$parent = end($pieces);
			$parentName = static::getPicklistValue($templateId, $moduleName)[$parent];
			$parentName = '(' . \App\Language::translate($parentName, $moduleName) . ') ';
		}
		$value['name'] = $parentName . \App\Language::translate($row['name'], $moduleName);
		if ($row['icon']) {
			if ($row['icon'] && strpos($row['icon'], 'layouts') === 0) {
				$basePath = '';
				if (!IS_PUBLIC_DIR) {
					$basePath = 'public_html/';
				}
				$value['icon'] = '<img class="treeImageIcon" src="' . $basePath . $row['icon'] . '" />';
			} else {
				$value['icon'] = '<span class="treeImageIcon ' . $row['icon'] . '"></span>';
			}
		}
		return $value;
	}

	/**
	 * Delete trees of the module.
	 *
	 * @param int $moduleId
	 */
	public static function deleteForModule($moduleId)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('vtiger_trees_templates', ['module' => $moduleId])->execute();
	}
}
