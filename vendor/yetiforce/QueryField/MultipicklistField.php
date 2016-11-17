<?php
namespace App\QueryField;

/**
 * Multipicklist Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class MultipicklistField extends BaseField
{

	/**
	 * Function to get combinations of string from Array
	 * @param array $array
	 * @param string $tempString
	 * @return array
	 */
	public static function getCombinations($array, $tempString = '')
	{
		$countArray = count($array);
		for ($i = 0; $i < $countArray; $i++) {
			$splicedArray = $array;
			$element = array_splice($splicedArray, $i, 1); // removes and returns the i'th element
			if (count($splicedArray) > 0) {
				if (!is_array($result)) {
					$result = [];
				}
				$result = array_merge($result, static::getCombinations($splicedArray, $tempString . ' |##| ' . $element[0]));
			} else {
				return [$tempString . ' |##| ' . $element[0]];
			}
		}
		return $result;
	}

	/**
	 * Get value
	 * @return mixed
	 */
	public function getValue()
	{
		$value = $this->value;
		$valueArray = explode(',', $value);
		if (in_array($this->operator, ['e', 'n'])) {
			foreach (static::getCombinations($valueArray) as $key => $value) {
				$valueArray[$key] = ltrim($value, ' |##| ');
			}
		}
		return $valueArray;
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		return ['not', [$this->getColumnName() => $this->getValue()]];
	}

	/**
	 * Starts with operator
	 * @return array
	 */
	public function operatorS()
	{
		return ['like', $this->getColumnName(), implode(' |##| ', $this->getValue()) . '%', false];
	}

	/**
	 * Ends with operator
	 * @return array
	 */
	public function operatorEw()
	{
		return ['like', $this->getColumnName(), '%' . implode(' |##| ', $this->getValue()), false];
	}

	/**
	 * Contains operator
	 * @return array
	 */
	public function operatorC()
	{
		return ['or like', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Does not contain operator
	 * @return array
	 */
	public function operatorK()
	{
		return ['or not like', $this->getColumnName(), $this->getValue()];
	}
}
