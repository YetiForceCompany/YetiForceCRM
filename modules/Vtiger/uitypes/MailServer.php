<?php

/**
 * UIType Mail server field file.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * UIType Mail server field class.
 */
class Vtiger_MailServer_UIType extends Vtiger_Base_UIType
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
		$displayValue = $value ? $this->getPicklistValues()[$value] ?? '' : '';
		if (!$rawText && $value && !$displayValue) {
			$displayValue = '<i class="color-red-500">' . \App\Language::translate('LBL_RECORD_DOES_NOT_EXIST', '_Base', null, false) . '</i>';
		} elseif (\is_int($length)) {
			$displayValue = \App\TextUtils::textTruncate($displayValue, $length);
		}

		return $rawText ? $displayValue : \App\Purifier::encodeHtml($displayValue);
	}

	/** {@inheritdoc} */
	public function getPicklistValues()
	{
		return array_map(fn ($server) => $server['name'], \App\Mail\Server::getAll(\App\Mail\Server::STATUS_ACTIVE, \App\Mail\Server::USER_VISIBLE));
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/PickList.tpl';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Picklist.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['integer'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/Picklist.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny'];
	}

	/** {@inheritdoc} */
	public function getFieldInfo(): array
	{
		$fieldInfo = $this->getFieldModel()->loadFieldInfo();
		$fieldInfo['picklistvalues'] = $this->getPicklistValues();

		return $fieldInfo;
	}
}
