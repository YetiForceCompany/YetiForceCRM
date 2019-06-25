<?php

/**
 * Date field condition record field class.
 *
 * @package   App
 */

namespace App\Conditions\RecordFields;

use App\Log;

/**
 * Date field condition record field class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class DateField extends BaseField
{
	/**
	 * {@inheritdoc}
	 */
	public function check()
	{
		$fn = 'operator' . ucfirst($this->operator);
		if (method_exists($this, $fn)) {
			Log::trace("Entering to $fn in " . __CLASS__);
			return $this->{$fn}();
		}
		Log::error("Not found operator: $fn in  " . __CLASS__);
		return false;
	}

	/**
	 * Custom operator.
	 *
	 * @return bool
	 */
	public function operatorCustom()
	{
		[$startDate, $endDate] = explode(',', $this->value);
		$dateValue = strtotime($this->getValue());
		return (strtotime($startDate) <= $dateValue) && (strtotime($endDate) >= $dateValue);
	}

	/**
	 * Today operator.
	 *
	 * @return bool
	 */
	public function operatorToday()
	{
		return date('Y-m-d', strtotime($this->getValue())) === date('Y-m-d');
	}

	/**
	 * Smaller operator.
	 *
	 * @return bool
	 */
	public function operatorSmaller()
	{
		return date('Y-m-d', strtotime($this->getValue())) < date('Y-m-d');
	}

	/**
	 * Greater operator.
	 *
	 * @return bool
	 */
	public function operatorGreater()
	{
		return date('Y-m-d', strtotime($this->getValue())) > date('Y-m-d');
	}

	/**
	 * Greater operator.
	 *
	 * @return bool
	 */
	public function operatorPrevfy()
	{
		return date('Y', strtotime($this->getValue())) === date('Y', strtotime('-1 year'));
	}

	/**
	 * Thisfy operator.
	 *
	 * @return bool
	 */
	public function operatorThisfy()
	{
		return date('Y', strtotime($this->getValue())) === date('Y');
	}

	/**
	 * Nextfy operator.
	 *
	 * @return bool
	 */
	public function operatorNextfy()
	{
		return date('Y', strtotime($this->getValue())) === date('Y', strtotime('+1 year'));
	}

	/**
	 * Prevfq operator.
	 *
	 * @return bool
	 */
	public function operatorPrevfq()
	{
		return (ceil(date('n', strtotime($this->value)) / 3)) === (ceil(date('n') / 3) - 1);
	}

	/**
	 * Thisfq operator.
	 *
	 * @return bool
	 */
	public function operatorThisfq()
	{
		return (ceil(date('n', strtotime($this->value)) / 3)) === (ceil(date('n') / 3));
	}

	/**
	 * Nextfq operator.
	 *
	 * @return bool
	 */
	public function operatorNextfq()
	{
		return (ceil(date('n', strtotime($this->value)) / 3)) === (ceil(date('n') / 3) + 1);
	}

	/**
	 * Yesterday operator.
	 *
	 * @return bool
	 */
	public function operatorYesterday()
	{
		return date('Y-m-d', strtotime($this->getValue())) === date('Y-m-d', strtotime('last day'));
	}

	/**
	 * Until operator.
	 *
	 * @return bool
	 */
	public function operatorUntil()
	{
		return date('Y-m-d', strtotime($this->getValue())) <= date('Y-m-d');
	}

	/**
	 * Tomorrow operator.
	 *
	 * @return bool
	 */
	public function operatorTomorrow()
	{
		return date('Y-m-d', strtotime($this->getValue())) === date('Y-m-d', strtotime('tomorrow'));
	}
}
