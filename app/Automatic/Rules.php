<?php
/**
 * A file that supports rules for statuses.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App\Automatic;

/**
 * A class that supports rules for statuses.
 */
class Rules
{
	/**
	 * Check if the value in the input array exists.
	 */
	public const CONDITION_IS_EXIST = 'isExist';

	/**
	 * Check if the value in the input array does not exist.
	 */
	public const CONDITION_NOT_IS_EXIST = 'notIsExist';

	/**
	 * Check if the elements of the input array are equal to the value.
	 */
	public const CONDITION_ALL_RECORDS_EQUAL = 'allRecordsEqual';

	/**
	 * Check if the elements of the input array are not equal to the value.
	 */
	public const CONDITION_NOT_ALL_RECORDS_EQUAL = 'notAllRecordsEqual';

	/**
	 * Check the input array contains values.
	 */
	public const CONDITION_ONLY_CONTAINS = 'onlyContains';

	/**
	 * Check the input array does not contain values.
	 */
	public const CONDITION_NOT_ONLY_CONTAINS = 'notOnlyContains';

	/**
	 * Check if the input array is empty.
	 */
	public const CONDITION_NO_RECORDS = 'noRecords';

	/**
	 * Check if the input array is not empty.
	 */
	public const CONDITION_NOT_NO_RECORDS = 'notNoRecords';

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
	public function __construct(array $config, $defaultValue = false)
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
		$condition = $params['condition'] ?? '';
		$returnVal = false;
		foreach ($params['rules'] as $rule) {
			$returnVal = $this->checkOperator($rule['operator'] ?? '', $items, $rule['value'] ?? '');
			if ('or' === $condition && $returnVal) {
				break;
			}
			if ('and' === $condition && !$returnVal) {
				break;
			}
		}
		return $returnVal;
	}

	/**
	 * Check the condition.
	 *
	 * @param string $operator
	 * @param array  $items
	 * @param mixed  $val
	 *
	 * @return bool
	 */
	protected function checkOperator(string $operator, array $items, $val): bool
	{
		if ($negation = $this->isNegation($operator)) {
			$operator = \substr($operator, 3, \strlen($operator) - 3);
		}
		$methodName = 'operator' . ucfirst($operator);
		if (\method_exists($this, $methodName)) {
			return $negation ? !$this->{$methodName}($items, $val) : $this->{$methodName}($items, $val);
		}
		throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||$operator", 406);
	}

	/**
	 * Check if there is a negation operator.
	 *
	 * @param string $operator
	 *
	 * @return bool
	 */
	private function isNegation(string $operator): bool
	{
		return \strlen($operator) >= 3 && 'not' === \substr($operator, 0, 3);
	}

	/**
	 * Check condition only contains.
	 *
	 * @param array $items
	 * @param mixed $valueOfCondition
	 *
	 * @return bool
	 */
	protected function operatorOnlyContains(array $items, $valueOfCondition): bool
	{
		$arrU = array_unique($items);
		return empty(array_diff($arrU, $valueOfCondition)) && empty(array_diff($valueOfCondition, $arrU));
	}

	/**
	 * Check the condition if it exists in the array.
	 *
	 * @param array $items
	 * @param mixed $valueOfCondition
	 *
	 * @return bool
	 */
	protected function operatorIsExist(array $items, $valueOfCondition): bool
	{
		return \in_array($valueOfCondition, $items);
	}

	/**
	 * Check the condition all are equal.
	 *
	 * @param array $items
	 * @param mixed $valueOfCondition
	 *
	 * @return bool
	 */
	protected function operatorAllRecordsEqual(array $items, $valueOfCondition): bool
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
	protected function operatorNoRecords(array $items): bool
	{
		return empty($items);
	}
}
