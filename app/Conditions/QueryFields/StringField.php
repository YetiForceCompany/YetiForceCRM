<?php

namespace App\Conditions\QueryFields;

/**
 * String Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class StringField extends BaseField
{
	/**
	 * Starts with operator.
	 *
	 * @return array
	 */
	public function operatorS()
	{
		$values = $this->getValue();
		if (\is_array($values)) { // Used only to filter the first letter of the name
			$condition = ['or'];
			foreach ($values as $value) {
				$condition[] = ['like', $this->getColumnName(), $value . '%', false];
			}

			return $condition;
		}
		return ['like', $this->getColumnName(), $this->getValue() . '%', false];
	}

	/**
	 * Ends with operator.
	 *
	 * @return array
	 */
	public function operatorEw()
	{
		return ['like', $this->getColumnName(), '%' . $this->getValue(), false];
	}
}
