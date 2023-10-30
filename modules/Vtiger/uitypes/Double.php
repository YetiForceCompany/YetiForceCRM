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

class Vtiger_Double_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return App\Fields\Double::formatToDb($value);
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$this->validate($value, true);
		preg_match_all('/[^\-\d]+/', $value, $matches);
		$matches[0] = array_map('trim', $matches[0]);
		if ($matches && $operators = \array_intersect(array_map('App\\Purifier::decodeHtml', $matches[0]), App\Conditions\QueryFields\IntegerField::$extendedOperators)) {
			$value = \App\Purifier::decodeHtml($value);
			$valueConvert = [];
			$operators = array_values($operators);
			$explodeBySpace = explode(' ', $value);
			foreach ($explodeBySpace as $key => $valueToCondition) {
				$ev = explode($operators[$key], $valueToCondition);
				$valueConvert[] = $operators[$key] . $this->getDBValue($ev[1]) . '';
			}
			return implode(' ', $valueConvert);
		}
		return $this->getDBValue($value);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate["$value"])) {
			return;
		}
		if ($isUserFormat) {
			$value = App\Fields\Double::formatToDb($value);
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
		return App\Fields\Double::formatToDisplay($value);
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return App\Fields\Double::formatToDisplay($value, false);
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Double.tpl';
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
}
