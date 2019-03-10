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

class Vtiger_Double_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return App\Fields\Double::formatToDb($value);
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
			$value = App\Fields\Double::formatToDb($value);
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = (float) $this->getFieldModel()->get('maximumlength') + 1;
		if ($maximumLength && ($value > $maximumLength || $value < -$maximumLength)) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		return App\Fields\Double::formatToDisplay($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return App\Fields\Double::formatToDisplay($value, false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Double.tpl';
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
	 * Generate valid sample value.
	 *
	 * @throws \Exception
	 *
	 * @return string
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
		return \App\Fields\Double::formatToDisplay(random_int($min, (int) $max - 1) . '.' . random_int(0, 9) . random_int(0, 9));
	}
}
