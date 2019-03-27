<?php
/**
 * A class that supports rules for statuses, cooperating with picklist.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App\Rules;

/**
 * Class RulesPicklist.
 */
class RulesPicklist extends \App\Rules
{
	/**
	 * What value to choose from the plicklist for rules.
	 */
	public const AUTOMATION = 'automation';

	/**
	 * {@inheritdoc}
	 */
	protected function check(array $items, array $params): bool
	{
		$condition = $params[static::CONDITION] ?? '';
		$b = false;
		foreach ($params[static::RULES] as $rule) {
			$val = $rule[static::VALUE] ?? '';
			$operator = $rule[static::OPERATOR] ?? '';
			$automation = $rule[static::AUTOMATION] ?? -1;
			$b = $this->checkOperator($operator, $val, $this->filterItems($items, $automation));
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
	 * Filter the array and get only the status.
	 *
	 * @param array $items
	 * @param int   $automation
	 *
	 * @return array
	 */
	protected function filterItems(array $items, int $automation = -1): array
	{
		$returnArr = [];
		foreach ($items as $item) {
			if (-1 === $automation || $item['automation'] === $automation) {
				$returnArr[] = $item['status'];
			}
		}
		return $returnArr;
	}
}
