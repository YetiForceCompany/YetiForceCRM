<?php

/**
 * UIType Tree Field Class
 * @package YetiForce.Fields
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Tree_UIType extends Vtiger_Base_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (substr($value, 0, 1) !== 'T' || !is_numeric(substr($value, 1))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->get('field')->getFieldName() . '||' . $value, 406);
		}
		$this->validate = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayValue($tree, $record = false, $recordInstance = false, $rawText = false)
	{
		$fieldModel = $this->get('field');
		$value = \App\Fields\Tree::getPicklistValueImage($fieldModel->getFieldParams(), $fieldModel->getModuleName(), $tree);
		if (isset($value['icon'])) {
			return $value['icon'] . '' . \App\Purifier::encodeHtml($value['name']);
		}
		return \App\Purifier::encodeHtml($value['name']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getListViewDisplayValue($tree, $record = false, $recordModel = false, $rawText = false)
	{
		$fieldModel = $this->get('field');
		$value = \App\Fields\Tree::getPicklistValueImage($fieldModel->getFieldParams(), $fieldModel->getModuleName(), $tree);
		if (isset($value['icon'])) {
			return $value['icon'] . '' . \vtlib\Functions::textLength(\App\Purifier::encodeHtml($value['name']), $this->get('field')->get('maxlengthtext'));
		}
		return \vtlib\Functions::textLength(\App\Purifier::encodeHtml($value['name']), $this->get('field')->get('maxlengthtext'));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'uitypes/TreeFieldSearchView.tpl';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateName()
	{
		return 'uitypes/Tree.tpl';
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAjaxEditable()
	{
		return false;
	}
}
