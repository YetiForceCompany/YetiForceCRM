<?php
/**
 * A file that supports rules for statuses, cooperating with picklist.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App\Automatic;

/**
 * A class that supports rules for statuses, cooperating with picklist.
 */
class RulesPicklist extends Rules
{
	/**
	 * What value to choose from the plicklist for rules. -1 = All, 1 = Closed , 0 = Open.
	 */
	public const AUTOMATION = 'automation';

	/** {@inheritdoc} */
	protected function check(array $items, array $params): bool
	{
		$condition = $params['condition'] ?? '';
		$b = false;
		foreach ($params['rules'] as $rule) {
			$val = $rule['value'] ?? '';
			$operator = $rule['operator'] ?? '';
			$automation = $rule[static::AUTOMATION] ?? -1;
			$b = $this->checkOperator($operator, $this->filterItems($items, $automation), $val);
			if ('or' === $condition && $b) {
				break;
			}
			if ('and' === $condition && !$b) {
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
