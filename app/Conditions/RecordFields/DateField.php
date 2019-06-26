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
	 * Today operator.
	 *
	 * @return bool
	 */
	public function operatorToday()
	{
		return $this->getValue() === date('Y-m-d');
	}
}
