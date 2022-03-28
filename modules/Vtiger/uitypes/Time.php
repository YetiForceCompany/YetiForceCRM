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

class Vtiger_Time_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (14 === $this->getFieldModel()->get('uitype')) {
			return self::getDBTimeFromUserValue($value);
		}
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 *  Function to get the DB Insert Value, for the current field type with given User Value for condition builder.
	 *
	 * @param mixed  $value
	 * @param string $operator
	 *
	 * @return string
	 */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		return \App\Purifier::purifyByType($value, 'TimeInUserFormat', true);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		$rawValue = $value;
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if ($isUserFormat) {
			$value = \App\Fields\Time::sanitizeDbFormat($value);
		}
		$timeFormat = 'H:i:s';
		$d = DateTime::createFromFormat($timeFormat, $value);
		if (!$d || $d->format($timeFormat) !== $value) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$rawValue] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = DateTimeField::convertToUserTimeZone(date('Y-m-d') . ' ' . $value)->format('H:i');
		if ('12' === App\User::getCurrentUserModel()->getDetail('hour_format')) {
			return self::getTimeValueInAMorPM($value);
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $this->getDisplayValue($value);
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Time.tpl';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Time.tpl';
	}

	public static function getDBTimeFromUserValue($value)
	{
		return DateTimeField::convertToDBTimeZone(date('Y-m-d') . ' ' . $value, null, false)->format('H:i:s');
	}

	/**
	 * Function to get display value for time.
	 *
	 * @param string time
	 * @param mixed $time
	 *
	 * @return string time
	 */
	public static function getDisplayTimeValue($time)
	{
		$date = new DateTimeField($time);

		return $date->getDisplayTime();
	}

	/**
	 * Function to get time value in AM/PM format.
	 *
	 * @param string $time
	 *
	 * @return string time
	 */
	public static function getTimeValueInAMorPM($time): string
	{
		$result = '';
		if ($time) {
			[$hours, $minutes, $seconds] = array_pad(explode(':', $time), 3, null);
			$format = \App\Language::translate('PM');

			if ($hours > 12) {
				$hours = (int) $hours - 12;
			} elseif ($hours < 12) {
				$format = \App\Language::translate('AM');
			}

			//If hours zero then we need to make it as 12 AM
			if ('00' == $hours) {
				$hours = '12';
				$format = \App\Language::translate('AM');
			}
			if (1 === \strlen($hours)) {
				$hours = "0$hours";
			}
			$result = "{$hours}:{$minutes}" . (null !== $seconds ? ":{$seconds}" : '') . " {$format}";
		}

		return $result;
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return null;
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return array_merge(['e', 'n', 'b', 'a', 'y', 'ny'], \App\Condition::FIELD_COMPARISON_OPERATORS);
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
		return 'ConditionBuilder/Time.tpl';
	}
}
