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
	 * @param string $tree
	 * @param int $record
	 * @param Vtiger_Record_Model $recordInstance
	 * @param boolean $rawText
	 * @return string
	 */
	public function getDisplayValue($tree, $record = false, $recordInstance = false, $rawText = false)
	{
		$fieldModel = $this->get('field');
		$treeData = \App\Fields\Tree::getPicklistValue($fieldModel->getFieldParams(), $fieldModel->getModuleName());
		return $treeData[$tree];
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
}
