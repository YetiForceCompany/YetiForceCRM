<?php

namespace App\QueryField;

/**
 * Integer Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class IntegerField extends BaseField
{
	public static $extendedOperators = ['>=', '<=', '<', '>'];

	/**
	 * Auto operator, it allows you to use formulas: >10<40, >1, <7.
	 *
	 * @return array
	 */
	public function operatorA()
	{
		$value = html_entity_decode($this->value);
		$condition = ['and'];
		$conditionFound = false;
		foreach (static::$extendedOperators as $exo) {
			if (strpos($value, $exo) !== false) {
				$ev = explode($exo, $value);
				$condition[] = [$exo, $this->getColumnName(), (int) $ev[1]];
				$value = str_replace($exo . (int) $ev[1], '', $value);
				$conditionFound = true;
			}
		}
		if (!$conditionFound) {
			return parent::operatorE();
		}
		return $condition;
	}

	/**
	 * Lower operator.
	 *
	 * @return array
	 */
	public function operatorL()
	{
		return ['<', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Greater operator.
	 *
	 * @return array
	 */
	public function operatorG()
	{
		return ['>', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Lower or equal operator.
	 *
	 * @return array
	 */
	public function operatorM()
	{
		return ['<=', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Greater or equal operator.
	 *
	 * @return array
	 */
	public function operatorH()
	{
		return ['>=', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Is empty operator.
	 *
	 * @return array
	 */
	public function operatorY()
	{
		return [$this->getColumnName() => null];
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy()
	{
		return ['not', [$this->getColumnName() => null]];
	}
}
