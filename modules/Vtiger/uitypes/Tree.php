<?php

/**
 * UIType Tree Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
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
				$parentName = '(' . vtranslate($parentName, $module) . ') ';
			}
			$name = $parentName . vtranslate($row['name'], $module);
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
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getAllValue()
	{
		$template = $this->get('field')->getFieldParams();
		$adb = PearDatabase::getInstance();
		$values = [];
		$result = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ?', array($template));
		while ($row = $adb->getRow($result)) {
			$tree = $row['tree'];
			$parent = '';
			$parentName = '';
			if ($row['depth'] > 0) {
				$parenttrre = $row['parenttrre'];
				$cut = strlen('::' . $tree);
				$parenttrre = substr($parenttrre, 0, - $cut);
				$pieces = explode('::', $parenttrre);
				$parent = end($pieces);
				$result3 = $adb->pquery("SELECT name FROM vtiger_trees_templates_data WHERE templateid = ? && tree = ?", array($template, $parent));
				$parentName = $adb->getSingleValue($result3);
				$parentName = '(' . vtranslate($parentName, $module) . ') ';
			}
			$values[$row['tree']] = array($parentName . vtranslate($row['name'], $this->get('field')->getModuleName()), $parent);
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
			return vtranslate($adb->query_result_raw($result, 0, 'name'), $module);
		}
		return false;
	}
}
