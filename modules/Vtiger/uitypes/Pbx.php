<?php

/**
 * UIType PBX field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * UIType PBX field class.
 */
class Vtiger_Pbx_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!isset($this->getPicklistValues()[$value])) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return parent::getDisplayValue(
			$this->getPicklistValues()[$value] ?? '',
			$record,
			$recordModel,
			$rawText,
			$length
		);
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
	public function getPicklistValues()
	{
		$pbx = [
			-1 => \App\Language::translate('LBL_PBX_BASE', 'Settings:PBX'),
			0 => \App\Language::translate('LBL_PBX_DEFAULT', 'Settings:PBX'),
		];
		foreach (\App\Integrations\Pbx::getAll() as $key => $row) {
			$pbx[$key] = $row['name'];
		}
		return $pbx;
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Picklist.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['smallint'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/Picklist.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny', 'ef', 'nf'];
	}
}
