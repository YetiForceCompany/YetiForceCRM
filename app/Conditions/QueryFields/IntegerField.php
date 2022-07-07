<?php
/**
 * Integer query field conditions file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * Integer query field conditions class.
 */
class IntegerField extends BaseField
{
	use \App\Conditions\QueryTraits\Comparison;
	use \App\Conditions\QueryTraits\ComparisonField;

	/**
	 * @var string[] List of extended operators
	 */
	public static $extendedOperators = ['>=', '<=', '<', '>'];

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
		foreach (static::$extendedOperators as $exo) {
			if (false !== strpos($value, $exo)) {
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

	/** {@inheritdoc} */
	public function getOperator(): string
	{
		return 'a' === $this->operator ? 'e' : $this->operator;
	}

	/**
	 * Is empty operator.
	 *
	 * @return array
	 */
	public function operatorY(): array
	{
		return [$this->getColumnName() => null];
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy(): array
	{
		return ['not', [$this->getColumnName() => null]];
	}
}
