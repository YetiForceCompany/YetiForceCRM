<?php

/**
 * UIType Category multipicklist
 * @package YetiForce.UIType
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_CategoryMultipicklist_UIType extends Vtiger_Tree_UIType
{
	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $tree
	 * @param int $record
	 * @param <Vtiger_Record_Model> $recordInstance
	 * @param boolean $rawText
	 * @return string
	 */
	public function getDisplayValue($tree, $record = false, $recordInstance = false, $rawText = false)
	{
		$template = $this->get('field')->getFieldParams();
		$module = $this->get('field')->getModuleName();
		$trees = explode(',', $tree);
		$names = [];
		foreach ($trees as $treeId) {
			$name = Vtiger_Cache::get('TreeData' . $template, $treeId);
			if ($name) {
				$names[] = $name;
			} else {
				$row = (new \App\Db\Query())->from('vtiger_trees_templates_data')
						->where(['templateid' => $template, 'tree' => $treeId])
						->one();
				$parentName = '';
				$name = false;
				if ($row !== false) {
					if ($row['depth'] > 0) {
						$parenttrre = $row['parenttrre'];
						$pieces = explode('::', $parenttrre);
						end($pieces);
						$parent = prev($pieces);
						$parentName = (new \App\Db\Query())->from('vtiger_trees_templates_data')
								->where(['templateid' => $template, 'tree' => $parent])
								->scalar();
						$parentName = '(' . vtranslate($parentName, $module) . ') ';
					}
					$name = $parentName . vtranslate($row['name'], $module);
				}
				Vtiger_Cache::set('TreeData' . $template, $treeId, $name);
				$names[] = $name;
			}
		}
		return implode(', ', $names);
	}
}
