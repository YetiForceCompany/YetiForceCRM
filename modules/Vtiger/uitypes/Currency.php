<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Currency_UIType extends Vtiger_Base_UIType
{
	protected $edit = false;

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return CurrencyField::convertToDBFormat($value, null, $this->getFieldModel()->get('uitype') === 72);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if ($isUserFormat) {
			$value = App\Fields\Currency::formatToDb($value);
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		if (($maximumLength = $this->getFieldModel()->get('maximumlength')) && ($value > $maximumLength || $value < -$maximumLength)) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (!$value) {
			return 0;
		}
		$uiType = $this->getFieldModel()->get('uitype');
		// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
		$value = CurrencyField::convertToUserFormat($value, null, $uiType === 72);
		if (!$this->edit) {
			$value = $this->getDetailViewDisplayValue($value, $record, $uiType);
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (!empty($value)) {
			$this->edit = true;
			return $this->getDisplayValue($value);
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function that converts the Number into Users Currency along with currency symbol.
	 *
	 * @param int|string $value
	 * @param int        $recordId
	 * @param int        $uiType
	 *
	 * @return Formatted Currency
	 */
	public function getDetailViewDisplayValue($value, $recordId, $uiType)
	{
		if ($uiType === 72 && $recordId) {
			$moduleName = $this->getFieldModel()->getModuleName();
			if (!$moduleName) {
				$moduleName = \App\Record::getType($recordId);
			}
			$currencyId = \App\Fields\Currency::getCurrencyByModule($recordId, $moduleName);
			$currencySymbol = \App\Fields\Currency::getById($currencyId)['currency_symbol'];
		} else {
			$currencyModal = new CurrencyField($value);
			$currencyModal->initialize();
			$currencySymbol = $currencyModal->currencySymbol;
		}
		return CurrencyField::appendCurrencySymbol($value, $currencySymbol);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Currency.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return ['decimal'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'];
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
		return 'ConditionBuilder/Currency.tpl';
	}

	/**
	 * Generate valid sample value.
	 *
	 * @throws \Exception
	 *
	 * @return float|null
	 */
	public function getSampleValue()
	{
		$min = 0;
		$max = $this->getFieldModel()->get('maximumlength');
		if (strpos($max, ',')) {
			$max = explode(',', $max)[1];
		}
		if ($max > 9999) {
			$max = 9999;
		}
		return \App\Fields\Currency::formatToDb(random_int($min, (int) $max - 1) . \App\User::getCurrentUserModel()->getDetail('currency_decimal_separator') . random_int(0, 9) . random_int(0, 9));
	}
}
