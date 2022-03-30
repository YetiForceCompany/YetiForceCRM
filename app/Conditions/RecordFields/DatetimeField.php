<?php

namespace App\Conditions\RecordFields;

/**
 * Datetime field condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class DatetimeField extends DateField
{
	/**
	 * Custom operator.
	 *
	 * @return bool
	 */
	public function operatorCustom()
	{
		[$startDate, $endDate] = explode(',', $this->value);
		$dateValue = strtotime($this->getValue());
		return ($dateValue >= strtotime($startDate)) && ($dateValue <= strtotime($endDate));
	}

	/**
	 * Smaller operator.
	 *
	 * @return bool
	 */
	public function operatorSmaller()
	{
		return strtotime($this->getValue()) < strtotime('now');
	}

	/**
	 * Greater operator.
	 *
	 * @return bool
	 */
	public function operatorGreater()
	{
		return strtotime($this->getValue()) > strtotime('now');
	}
}
