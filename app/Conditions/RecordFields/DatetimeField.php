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
	 * Between operator.
	 *
	 * @return array
	 */
	public function operatorBw()
	{
		[$startDate, $endDate] = explode(',', $this->value);
		$dateValue = date('Y-m-d H:i:s', strtotime($this->getValue()));
		return ($dateValue >= date('Y-m-d H:i:s', strtotime($startDate))) && ($dateValue <= date('Y-m-d H:i:s', strtotime($endDate)));
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
