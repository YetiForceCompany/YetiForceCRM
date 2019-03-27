<?php

/**
 * A class that supports rules for statuses.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App;

/**
 * Class Rules.
 */
class Rules
{
	public const CONDITION_IS_EXIST = 'is_exist';
	public const CONDITION_NOT_IS_EXIST = 'not_is_exist';
	public const CONDITION_ALL_EQUAL = 'all_equal';
	public const CONDITION_NOT_ALL_EQUAL = 'not_all_equal';
	public const CONDITION_ONLY_CONTAINS = 'only_contains';
	public const CONDITION_NOT_ONLY_CONTAINS = 'not_only_contains';
	public const CONDITION_EMPTY = 'empty';
	public const CONDITION_NOT_EMPTY = 'not_empty';
	public const VALUE = 'value';
	public const OPERATOR = 'operator';
	public const CONDITION = 'condition';
	public const RULES = 'rules';
	public const OR = 'or';
	public const AND = 'and';

	/**
	 * Rules configuration.
	 *
	 * @var array
	 */
	private $config = [];

	/**
	 * The default value is returned if the rules do not specify this case.
	 *
	 * @var mixed
	 */
	private $defaultValue;

	/**
	 * Constructor.
	 *
	 * @param array $config
	 * @param mixed $defaultValue
	 */
	public function __construct(array $config, $defaultValue = 'UNDEFINED')
	{
		$this->config = $config;
		$this->defaultValue = $defaultValue;
	}

	/**
	 * Get value.
	 *
	 * @param array $items
	 *
	 * @return mixed
	 */
	public function getValue(array $items)
	{
		$returnVal = $this->defaultValue;
		foreach ($this->config as $key => $params) {
			if ($this->check($items, $params)) {
				$returnVal = $key;
				break;
			}
		}
		return $returnVal;
	}

	/**
	 * Check rules.
	 *
	 * @param array $items
	 * @param array $params
	 *
	 * @return bool
	 */
	protected function check(array $items, array $params): bool
	{
		$condition = $params[static::CONDITION] ?? '';
		$b = false;
		foreach ($params[static::RULES] as $rule) {
			$val = $rule[static::VALUE] ?? '';
			$operator = $rule[static::OPERATOR] ?? '';
			$b = $this->checkOperator($operator, $val, $items);
			if (static::OR === $condition && $b) {
				break;
			}
			if (static::AND === $condition && !$b) {
				break;
			}
		}
		return $b;
	}

	/**
	 * Check the condition.
	 *
	 * @param string $operator
	 * @param mixed  $val
	 * @param array  $items
	 *
	 * @return bool
	 */
	protected function checkOperator(string $operator, $val, array $items): bool
	{
		$b = false;
		switch ($operator) {
			case static::CONDITION_IS_EXIST:
				$b = $this->operatorIsExist($val, $items);
			break;
			case static::CONDITION_NOT_IS_EXIST:
				$b = !$this->operatorIsExist($val, $items);
			break;
			case static::CONDITION_ALL_EQUAL:
				$b = $this->operatorAllEqual($val, $items);
			break;
			case static::CONDITION_NOT_ALL_EQUAL:
				$b = !$this->operatorAllEqual($val, $items);
			break;
			case static::CONDITION_ONLY_CONTAINS:
				$b = $this->operatorOnlyContains($val, $items);
			break;
			case static::CONDITION_NOT_ONLY_CONTAINS:
				$b = !$this->operatorOnlyContains($val, $items);
			break;
			case static::CONDITION_EMPTY:
				$b = $this->operatorEmpty($items);
			break;
			case static::CONDITION_NOT_EMPTY:
				$b = !$this->operatorEmpty($items);
			break;
			default:
				throw new Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$operator", 406);
		}
		return $b;
	}

	/**
	 * Check condition only contains.
	 *
	 * @param mixed $valueOfCondition
	 * @param array $items
	 *
	 * @return bool
	 */
	protected function operatorOnlyContains($valueOfCondition, array $items): bool
	{
		$arrU = array_unique($items);
		return empty(array_diff($arrU, $valueOfCondition)) && empty(array_diff($valueOfCondition, $arrU));
	}

	/**
	 * Check the condition if it exists in the array.
	 *
	 * @param mixed $valueOfCondition
	 * @param array $items
	 *
	 * @return bool
	 */
	protected function operatorIsExist($valueOfCondition, array $items): bool
	{
		return in_array($valueOfCondition, $items);
	}

	/**
	 * Check the condition all are equal.
	 *
	 * @param mixed $valueOfCondition
	 * @param array $items
	 *
	 * @return bool
	 */
	protected function operatorAllEqual($valueOfCondition, array $items): bool
	{
		$b = !empty($items);
		foreach ($items as $val) {
			if ($val !== $valueOfCondition) {
				$b = false;
				break;
			}
		}
		return $b;
	}

	/**
	 * Check the condition that the array is empty.
	 *
	 * @param array $items
	 *
	 * @return bool
	 */
	protected function operatorEmpty(array $items): bool
	{
		return empty($items);
	}
}
