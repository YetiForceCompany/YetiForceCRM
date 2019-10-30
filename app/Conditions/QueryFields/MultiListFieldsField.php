<?php
/**
 * MultiListFields query field.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

/**
 * MultiListFieldsField class.
 */
class MultiListFieldsField extends MultipicklistField
{
	/**
	 * Separator.
	 *
	 * @var string
	 */
	protected $separator = ',';

	/**
	 * Function to get combinations of string from Array.
	 *
	 * @param array  $array
	 * @param string $tempString
	 *
	 * @return array
	 */
	public function getCombinations($array, $tempString = '')
	{
		$countArray = \count($array);
		$result = [];
		for ($i = 0; $i < $countArray; ++$i) {
			$splicedArray = $array;
			$element = array_splice($splicedArray, $i, 1);
			if (\count($splicedArray) > 0) {
				$result = array_merge($result, $this->getCombinations($splicedArray, $tempString . $this->separator . $element[0]));
			} else {
				return [$tempString . $this->separator . $element[0]];
			}
		}
		return $result ?? [];
	}

	/**
	 * Get value.
	 *
	 * @return array
	 */
	public function getValue(): array
	{
		$value = $this->value;
		$valueArray = array_filter(explode(',', $value));
		if (\in_array($this->operator, ['e', 'n'])) {
			foreach ($this->getCombinations($valueArray) as $key => $value) {
				if (!empty($value)) {
					$valueArray[$key] = ltrim($value, $this->separator);
				}
			}
		}
		return $valueArray;
	}
}
