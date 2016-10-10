<?php

/**
 * UIType Category multipicklist
 * @package YetiForce.UIType
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class Vtiger_CategoryMultipicklist_UIType extends Vtiger_Tree_UIType
{

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($tree, $record = false, $recordInstance = false, $rawText = false)
	{
		$template = $this->get('field')->getFieldParams();
		$module = $this->get('field')->getModuleName();
		$trees = explode(',', $tree);
		$names = [];
		$db = null;
		foreach ($trees as $treeId) {
			$name = Vtiger_Cache::get('TreeData' . $template, $treeId);
			if ($name) {
				$names[] = $name;
			} else {
				if (!$db) {
					$db = PearDatabase::getInstance();
				}
				$result = $db->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ? && tree = ?', [$template, $treeId]);
				$parentName = '';
				$name = false;
				if ($db->getRowCount($result)) {
					$row = $db->getRow($result);
					if ($row['depth'] > 0) {
						$parenttrre = $row['parenttrre'];
						$pieces = explode('::', $parenttrre);
						end($pieces);
						$parent = prev($pieces);
						$result2 = $db->pquery('SELECT name FROM vtiger_trees_templates_data WHERE templateid = ? && tree = ?', [$template, $parent]);
						$parentName = $db->getSingleValue($result2);
						$parentName = '(' . vtranslate($parentName, $module) . ') ';
					}
					$name = $parentName . vtranslate($row['name'], $module);
				}
				Vtiger_Cache::set('TreeData' . $template, $treeId, $name);
				$names[] = $name;
			}
		}
		$names = implode(', ', $names);
		return $names;
	}
}
