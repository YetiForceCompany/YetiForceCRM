<?php

/**
 * UIType Category multipicklist.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_CategoryMultipicklist_UIType extends Vtiger_Tree_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if ($value) {
			$value = trim($value, ',');
			$value = ",{$value},";
		} elseif (null === $value) {
			$value = '';
		}
		return \App\Purifier::decodeHtml($value);
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$values = [];
		if (!\is_array($value)) {
			$value = $value ? explode(',', $value) : [];
		}
		foreach ($value as $val) {
			$this->validate($val, true);
			$values[] = \App\Purifier::decodeHtml($val);
		}
		return implode('##', $values);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (isset($this->validate[$value]) || '' === $value || null === $value) {
			return;
		}
		foreach (explode(',', $value) as $row) {
			if ($row && ('T' !== substr($row, 0, 1) || !is_numeric(substr($row, 1)))) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$fieldModel = $this->getFieldModel();
		$names = [];
		$trees = array_filter(explode(',', $value));
		$treeData = \App\Fields\Tree::getPicklistValue($fieldModel->getFieldParams(), $fieldModel->getModuleName());
		foreach ($trees as $treeId) {
			if (isset($treeData[$treeId])) {
				$names[] = $treeData[$treeId];
			}
		}
		$value = implode(', ', $names);
		if (\is_int($length)) {
			$value = \App\TextUtils::textTruncate($value, $length);
		}
		return $rawText ? $value : \App\Purifier::encodeHtml($value);
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'c', 'ch', 'k', 'kh', 'y', 'ny', 'ef', 'nf'];
	}
}
