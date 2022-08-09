<?php

/**
 * UIType Tree Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$maximumLength = $this->getFieldModel()->getMaxValue();
		if ($maximumLength && App\TextUtils::getTextLength($value) > $maximumLength) {
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
					$text = \App\TextUtils::textTruncate($text, $length);
				}
				return $text;
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
			$text = \App\TextUtils::textTruncate($text, $length);
		}

		return $rawText ? $text : ($value['icon'] ?? '') . \App\Purifier::encodeHtml($text);
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
		$parts = explode(',', trim($value, ', '));
		$values = \App\Fields\Tree::getValuesById((int) $this->getFieldModel()->getFieldParams());
		foreach ($parts as &$part) {
			foreach ($values as $id => $treeRow) {
				if ($part === $id) {
					$part = $treeRow['name'];
				}
			}
		}
		return implode(' |##| ', $parts);
	}

	/** {@inheritdoc} */
	public function getApiEditValue($value)
	{
		if (empty($value)) {
			return ['value' => ''];
		}
		$tree = \App\Fields\Tree::getPicklistValueImage($this->getFieldModel()->getFieldParams(), $this->getFieldModel()->getModuleName(), $value);
		return [
			'value' => $tree['name'],
			'raw' => $value,
		];
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		if ('' === $value && null !== $defaultValue) {
			$value = $defaultValue;
		}
		$values = explode(' |##| ', trim($value));
		$fieldValue = '';
		$trees = \App\Fields\Tree::getValuesById((int) $this->getFieldModel()->getFieldParams());
		foreach ($trees as $tree) {
			foreach ($values as $value) {
				if ($tree['name'] === $value) {
					$fieldValue .= $tree['tree'] . ',';
					break;
				}
			}
		}
		if ('tree' === $this->getFieldModel()->getFieldDataType()) {
			$fieldValue = trim($fieldValue, ',');
		} else {
			if ($fieldValue) {
				$fieldValue = ',' . $fieldValue;
			}
		}
		return $fieldValue;
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
		return ['e', 'n', 'y', 'ny', 'ef', 'nf'];
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
