<?php
/**
 * Tree.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * @return array
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
				$cut = \strlen('::' . $tree);
				$parentTrre = substr($parentTrre, 0, -$cut);
				$pieces = explode('::', $parentTrre);
				$parent = end($pieces);
				$parentName = $dataTree[$parent]['name'];
				$parentName = '(' . \App\Language::translate($parentName, $moduleName, null, false) . ') ';
			}
			$values[$row['tree']] = $parentName . \App\Language::translate($row['name'], $moduleName, null, false);
		}
		return $values;
	}

	/**
	 * Get tree values for jstree.
	 *
	 * @param int    $templateId
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getTreeValues(int $templateId, string $moduleName): array
	{
		$dataTree = static::getValuesById($templateId);
		$tree = [];
		foreach ($dataTree as $row) {
			$parentIdx = static::getParentIdx($row);
			$tree[] = [
				'id' => (int) str_replace('T', '', $row['tree']),
				'tree' => $row['tree'],
				'parent' => false === $parentIdx ? '#' : (int) str_replace('T', '', $dataTree[$parentIdx]['tree']),
				'text' => \App\Language::translate($row['name'], $moduleName, null, false),
			];
		}
		return $tree;
	}

	/**
	 * Get parent index.
	 *
	 * @param array $itemTree
	 *
	 * @return flase|string
	 */
	public static function getParentIdx(array $itemTree)
	{
		$parentItem = explode('::', $itemTree['parentTree']);
		$parentIdx = \count($parentItem) - 2;
		return $parentIdx < 0 ? false : $parentItem[$parentIdx];
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
			$cut = \strlen('::' . $treeId);
			$parentTrre = substr($parentTrre, 0, -$cut);
			$pieces = explode('::', $parentTrre);
			$parent = end($pieces);
			$parentName = static::getPicklistValue($templateId, $moduleName)[$parent];
			$parentName = '(' . \App\Language::translate($parentName, $moduleName, null, false) . ') ';
		}
		$value['name'] = $parentName . \App\Language::translate($row['name'], $moduleName, null, false);
		if ($row['icon']) {
			if ($row['icon'] && false !== strpos($row['icon'], '/')) {
				$basePath = '';
				if (!IS_PUBLIC_DIR) {
					$basePath = 'public_html/';
				}
				$value['icon'] = '<img class="icon-img--picklist mr-1" src="' . $basePath . $row['icon'] . '" />';
			} else {
				$value['icon'] = '<span class="mr-1 ' . $row['icon'] . '"></span>';
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
		$db->createCommand()->delete('vtiger_trees_templates', ['tabid' => $moduleId])->execute();
	}
}
