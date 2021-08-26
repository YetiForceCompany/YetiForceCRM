<?php

namespace App\Conditions\RecordFields;

/**
 * Multi picklist condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class MultipicklistField extends BaseField
{
	/** {@inheritdoc} */
	public function operatorE(): bool
	{
		$check = false;
		foreach ($this->getValue() as $valueRecord) {
			if (\array_intersect(explode('##', $this->value), explode(' |##| ', $valueRecord))) {
				$check = true;
			}
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function operatorN(): bool
	{
		$check = false;
		if (!$this->operatorE()) {
			return true;
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function operatorC(): bool
	{
		$check = false;
		foreach ($this->getValue() as $valueRecord) {
			if (strpos($valueRecord, $this->value) || $valueRecord === $this->value) {
				$check = true;
			}
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function operatorK(): bool
	{
		$check = true;
		foreach ($this->getValue() as $valueRecord) {
			if ($valueRecord === $this->value) {
				return false;
			}
			if (!(false == strpos($valueRecord, $this->value))) {
				$check = false;
			}
		}
		return $check;
	}

	/** {@inheritdoc} */
	public function getValue(): array
	{
		$value = parent::getValue();
		$valueArray = explode(' |##| ', $value);
		if (\in_array($this->operator, ['e', 'n'])) {
			foreach ($this->getCombinations($valueArray) as $key => $value) {
				$valueArray[$key] = ltrim($value, ' |##| ');
			}
		}
		return $valueArray;
	}

	/**
	 * Function to get combinations of string from Array.
	 *
	 * @param array  $array
	 * @param string $tempString
	 *
	 * @return array
	 */
	public function getCombinations($array, $tempString = ''): array
	{
		$countArray = \count($array);
		$result = '';
		for ($i = 0; $i < $countArray; ++$i) {
			$splicedArray = $array;
			$element = array_splice($splicedArray, $i, 1);
			if (\count($splicedArray) > 0) {
				if (!\is_array($result)) {
					$result = [];
				}
				$result = array_merge($result, $this->getCombinations($splicedArray, $tempString . ' |##| ' . $element[0]));
			} else {
				return [$tempString . ' |##| ' . $element[0]];
			}
		}
		return $result;
	}
}
