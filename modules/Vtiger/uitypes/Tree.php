<?php

/**
 * UIType Tree Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Tree_UIType extends Vtiger_Base_UIType
{

	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Tree.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($tree, $record = false, $recordInstance = false, $rawText = false)
	{
		$template = $this->get('field')->getFieldParams();
		$name = Vtiger_Cache::get('TreeData' . $template, $tree);
		if ($name) {
			return $name;
		}

		$row = (new App\Db\Query())
			->from('vtiger_trees_templates_data')
			->where(['templateid' => $template, 'tree' => $tree])
			->one();
		$parentName = '';
		$module = $this->get('field')->getModuleName();
		$name = false;
		if ($row !== false) {
			if ($row['depth'] > 0) {
				$parenttrre = $row['parenttrre'];
				$pieces = explode('::', $parenttrre);
				end($pieces);
				$parent = prev($pieces);
				$parentName = (new App\Db\Query())
					->select('name')
					->from('vtiger_trees_templates_data')
					->where(['templateid' => $template, 'tree' => $parent])
					->scalar();
				$parentName = '(' . \App\Language::translate($parentName, $module) . ') ';
			}
			$name = $parentName . \App\Language::translate($row['name'], $module);
		}
		Vtiger_Cache::set('TreeData' . $template, $tree, $name);
		return $name;
	}

	/**
	 * Function to get the display value in edit view
	 * @param reference record id
	 * @return link
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		return $this->getDisplayValue($value, $record);
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/TreeFieldSearchView.tpl';
	}

	/**
	 * Function to get the all Values
	 * @return array
	 */
	public function getAllValue()
	{
		$template = $this->get('field')->getFieldParams();
		$values = [];
		$dataReader = (new \App\Db\Query())->from('vtiger_trees_templates_data')->where(['templateid' => $template])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$tree = $row['tree'];
			$parent = '';
			$parentName = '';
			if ($row['depth'] > 0) {
				$parenttrre = $row['parenttrre'];
				$cut = strlen('::' . $tree);
				$parenttrre = substr($parenttrre, 0, - $cut);
				$pieces = explode('::', $parenttrre);
				$parent = end($pieces);
				$parentName = (new \App\Db\Query())->select(['name'])->from('vtiger_trees_templates_data')->where(['templateid' => $template, 'tree' => $parent])->scalar();
				$parentName = '(' . \App\Language::translate($parentName, $module) . ') ';
			}
			$values[$row['tree']] = [$parentName . \App\Language::translate($row['name'], $this->get('field')->getModuleName()), $parent];
		}
		return $values;
	}

	public static function getDisplayValueByField($tree, $field, $module)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('SELECT fieldparams FROM vtiger_field WHERE tabid = ? && fieldname = ?', array(vtlib\Functions::getModuleId($module), $field));
		if ($adb->num_rows($result) == 0) {
			return false;
		}
		$template = $adb->query_result_raw($result, 0, 'fieldparams');
		$result = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ? && tree = ?', array($template, $tree));
		if ($adb->num_rows($result)) {
			return \App\Language::translate($adb->query_result_raw($result, 0, 'name'), $module);
		}
		return false;
	}
}
