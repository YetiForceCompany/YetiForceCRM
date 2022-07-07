<?php

namespace App\Conditions\QueryFields;

/**
 * Double Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
class DoubleField extends IntegerField
{
	/**
	 * Auto operator, it allows you to use formulas: >10 <40, >1, <7.
	 *
	 * @return array
	 */
	public function operatorA(): array
	{
		$value = \App\Purifier::decodeHtml($this->value);
		$condition = ['and'];
		$conditionFound = false;
		$explodeBySpace = explode(' ', $value);
		foreach ($explodeBySpace as $valueToCondition) {
			$operatorWasFound = false;
			foreach (static::$extendedOperators as $exo) {
				if (false !== strpos($valueToCondition, $exo) && false === $operatorWasFound) {
					$ev = explode($exo, $valueToCondition);
					$condition[] = [$exo, $this->getColumnName(),  $ev[1]];
					$conditionFound = true;
					$operatorWasFound = true;
				}
			}
		}
		if (!$conditionFound) {
			return parent::operatorE();
		}
		return $condition;
	}
}
