<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_Currency_UIType extends Vtiger_Base_UIType
{
	protected $edit = false;

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return CurrencyField::convertToDBFormat($value, null, 72 === $this->getFieldModel()->get('uitype'));
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate["$value"])) {
			return;
		}
		if ($isUserFormat) {
			$value = App\Fields\Currency::formatToDb($value);
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		if ($maximumLength = $this->getFieldModel()->get('maximumlength')) {
			[$minimumLength, $maximumLength] = false !== strpos($maximumLength, ',') ? explode(',', $maximumLength) : [-$maximumLength, $maximumLength];
			if ((float) $minimumLength > $value || (float) $maximumLength < $value) {
				throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . "||{$maximumLength} < {$value} < {$minimumLength}", 406);
			}
		}
		$this->validate["$value"] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (!$value) {
			return 0;
		}
		$uiType = $this->getFieldModel()->get('uitype');
		// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
		$value = \App\Fields\Currency::formatToDisplay($value, null, 72 === $uiType);
		if (!$this->edit) {
			$value = $this->getDetailViewDisplayValue($value, $record, $uiType);
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (!empty($value)) {
			$this->edit = true;
			return $this->getDisplayValue($value);
		}
		return $value;
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
		$moduleName = $this->getFieldModel()->getModuleName();
		if (!$moduleName) {
			$moduleName = \App\Record::getType($recordId);
		}
		if (72 === $uiType && $recordId && $currencyId = \App\Fields\Currency::getCurrencyByModule($recordId, $moduleName)) {
			$currencySymbol = \App\Fields\Currency::getById($currencyId)['currency_symbol'];
		} else {
			$userModel = \App\User::getCurrentUserModel();
			$currencySymbol = $userModel->getDetail('currency_symbol');
		}
		return CurrencyField::appendCurrencySymbol($value, $currencySymbol);
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Currency.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['decimal'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return array_merge(['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'], \App\Condition::FIELD_COMPARISON_OPERATORS);
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
}
