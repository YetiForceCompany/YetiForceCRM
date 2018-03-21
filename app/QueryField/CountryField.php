<?php
/**
 * Country query field class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\QueryField;

/**
 * Country query field class.
 */
class CountryField extends BaseField
{
	/**
	 * Auto operator.
	 *
	 * @return array
	 */
	public function operatorA()
	{
		if (strpos($this->value, '##') === false) {
			return [$this->getColumnName() => $this->value];
		}
		$values = explode('##', $this->value);
		$condition = ['or'];
		foreach ($values as $value) {
			$condition[] = [$this->getColumnName() => $value];
		}

		return $condition;
	}
}
