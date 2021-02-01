<?php

/**
 * UIType Tree Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Tree_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if ('T' !== substr($value, 0, 1) || !is_numeric(substr($value, 1))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength && App\TextParser::getTextLength($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$values = [];
		if (!\is_array($value)) {
			$value = $value ? explode('##', $value) : [];
		}
		foreach ($value as $val) {
			$values[] = parent::getDbConditionBuilderValue($val, $operator);
		}
		return implode('##', $values);
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$fieldModel = $this->getFieldModel();
		if (false === strpos($value, ',')) {
			if ($rawText) {
				$text = \App\Fields\Tree::getPicklistValue($fieldModel->getFieldParams(), $fieldModel->getModuleName())[$value];
				if (\is_int($length)) {
					$text = \App\TextParser::textTruncate($text, $length);
				}
				return \App\Purifier::encodeHtml($text);
			}
			$value = \App\Fields\Tree::getPicklistValueImage($fieldModel->getFieldParams(), $fieldModel->getModuleName(), $value);
			$text = $value['name'];
		} else {
			$names = [];
			$trees = array_filter(explode(',', $value));
			$treeData = \App\Fields\Tree::getPicklistValue($fieldModel->getFieldParams(), $fieldModel->getModuleName());
			foreach ($trees as $treeId) {
				if (isset($treeData[$treeId])) {
					$names[] = $treeData[$treeId];
				}
			}
			$text = implode(', ', $names);
		}
		if (\is_int($length)) {
			$text = \App\TextParser::textTruncate($text, $length);
		}
		if (isset($value['icon'])) {
			return $value['icon'] . '' . \App\Purifier::encodeHtml($text);
		}
		return \App\Purifier::encodeHtml($text);
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Tree.tpl';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Tree.tpl';
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny'];
	}

	/**
	 * Returns template for operator.
	 *
	 * @param string $operator
	 *
	 * @return string
	 */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/Tree.tpl';
	}
}
