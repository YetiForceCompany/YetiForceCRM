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

class Vtiger_Integer_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return App\Fields\Integer::formatToDb($value);
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$this->validate($value, true);
		preg_match_all('/\D+/', $value, $matches);
		$matches[0] = array_map('trim', $matches[0]);
		if ($matches && $operators = \array_intersect(array_map('App\\Purifier::decodeHtml', $matches[0]), App\Conditions\QueryFields\IntegerField::$extendedOperators)) {
			$value = \App\Purifier::decodeHtml($value);
			$valueConvert = [];
			$operators = array_values($operators);
			$explodeBySpace = explode(' ', $value);
			foreach ($explodeBySpace as $key => $valueToCondition) {
				$ev = explode($operators[$key], $valueToCondition);
				$valueConvert[] = $operators[$key] . (int) $ev[1] . '';
			}
			return implode(' ', $valueConvert);
		}
		return $this->getDBValue($value);
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return null === $value || '' === $value ? '' : App\Fields\Integer::formatToDisplay($value);
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return App\Fields\Integer::formatToDisplay($value);
	}

	/**
	 * Verification of data.
	 *
	 * @param string $value
	 * @param bool   $isUserFormat
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (isset($this->validate[$value]) || empty($value)) {
			return;
		}
		if ($isUserFormat) {
			$value = App\Fields\Integer::formatToDb($value);
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		if ($maximumLength = $this->getFieldModel()->get('maximumlength')) {
			$rangeValues = explode(',', $maximumLength);
			if (($rangeValues[1] ?? $rangeValues[0]) < $value || (isset($rangeValues[1]) ? $rangeValues[0] : 0) > $value) {
				throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . (isset($rangeValues[1]) ? $rangeValues[0] : 0) . ' < ' . $value . ' < ' . ($rangeValues[1] ?? $rangeValues[0]), 406);
			}
		}
		$this->validate[$value] = true;
	}

	/**
	 * Function to get the Template name for the current UI Type object.
	 *
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Number.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['bigint', 'integer', 'smallint', 'tinyint'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return array_merge(['e', 'n', 'l', 'g', 'm', 'h', 'y', 'ny'], \App\Condition::FIELD_COMPARISON_OPERATORS);
	}
}
