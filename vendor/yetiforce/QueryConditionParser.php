<?php
namespace App;

/**
 * Query condition parser class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class QueryConditionParser
{

	/**
	 * @var QueryGenerator 
	 */
	private $queryGenerator;

	/**
	 * @var \Vtiger_Field_Model 
	 */
	private $fieldModel;

	/**
	 * @var string|array 
	 */
	private $value;

	/**
	 * @var string 
	 */
	private $operator;

	const STRING_TYPE = ['string', 'text', 'email', 'reference'];
	const NUMERIC_TYPE = ['integer', 'double', 'currency'];
	const DATE_TYPE = ['date', 'datetime'];
	const EQUALITY_TYPES = ['currency', 'percentage', 'double', 'integer', 'number'];
	const COMMA_TYPES = ['picklist', 'multipicklist', 'owner', 'date', 'datetime', 'time', 'tree', 'sharedOwner', 'sharedOwner'];

	public function __construct(QueryGenerator $queryGenerator, \Vtiger_Field_Model $fieldModel, $value, $operator)
	{
		$this->queryGenerator = $queryGenerator;
		$this->fieldModel = $fieldModel;
		$this->value = $value;
		$this->operator = strtolower($operator);
	}

	/**
	 * Is the field date type
	 * @return boolean
	 */
	private function isDateType()
	{
		return in_array($this->fieldModel->getFieldDataType(), [static::DATE_TYPE]);
	}

	/**
	 * Is the field numeric type
	 * @return boolean
	 */
	private function isNumericType()
	{
		return in_array($this->fieldModel->getFieldDataType(), [static::NUMERIC_TYPE]);
	}

	/**
	 * Is the field string type
	 * @return boolean
	 */
	private function isStringType()
	{
		return in_array($this->fieldModel->getFieldDataType(), [static::STRING_TYPE]);
	}

	/**
	 * Is the field equality type
	 * @return boolean
	 */
	private function isEqualityType()
	{
		return in_array($this->fieldModel->getFieldDataType(), [static::EQUALITY_TYPES]);
	}

	/**
	 * Is the field comma separated type
	 * @return boolean
	 */
	private function isCommaSeparatedType()
	{
		return in_array($this->fieldModel->getFieldDataType(), [static::COMMA_TYPES]);
	}

	public function getNativeCondition()
	{
		if ($this->operator === 'between' && $this->isDateType()) {
			$start = explode(' ', $this->value[0]);
			if (isset($start[1])) {
				$this->value[0] = getValidDBInsertDateTimeValue($start[0] . ' ' . $start[1]);
			}
			$end = explode(' ', $this->value[1]);
			// Dates will be equal for Today, Tomorrow, Yesterday.
			if (isset($end[1])) {
				if ($start[0] === $end[0]) {
					$dateTime = new \DateTime($this->value[0]);
					$nextDay = $dateTime->modify('+1 days')->format('Y-m-d H:i:s');
					$values = explode(' ', $nextDay);
					$this->value[1] = getValidDBInsertDateTimeValue($values[0]) . ' ' . $values[1];
				} else {
					$end = $this->value[1];
					$dateObject = new \DateTimeField($end);
					$this->value[1] = $dateObject->getDBInsertDateTimeValue();
				}
			}
		}
		return $this->parseConditionValue();
	}

	private function parseConditionValue()
	{
		if (is_string($this->value) && !$this->queryGenerator->getIgnoreComma()) {
			$this->parseValue();
		} elseif ($this->operator === 'e' && $this->fieldModel->isReferenceField() && AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			$this->value = explode(',', $value);
		} elseif (!is_array($this->value)) {
			$this->value = [$this->value];
		}
		if ($this->operator === 'between' || $this->operator === 'bw' || $this->operator === 'notequal') {
			return $this->parseBetweenValue();
		}
	}

	private function parseValue()
	{
		if ($this->isCommaSeparatedType()) {
			$valueArray = explode(',', $this->value);
			if ($this->fieldModel->getFieldDataType() === 'multipicklist' && in_array($this->operator, ['e', 'n'])) {
				$valueArray = static::getCombinations($valueArray);
				foreach ($valueArray as &$value) {
					$value = ltrim($value, ' |##| ');
				}
			}
		} elseif ($this->fieldModel->getFieldDataType() === 'multiReferenceValue') {
			$valueArray = explode(', ', $this->value);
			foreach ($valueArray as &$value) {
				$value = '|#|' . $value . '|#|';
			}
		} else {
			$valueArray = [$this->value];
		}
		$this->value = $valueArray;
	}

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
				return array($tempString . ' |##| ' . $element[0]);
			}
		}
		return $result;
	}

	private function parseBetweenValue()
	{
		if ($this->fieldModel->getFieldName() === 'birthday') {
			$this->value[0] = getValidDBInsertDateTimeValue($this->value[0]);
			$this->value[1] = getValidDBInsertDateTimeValue($this->value[1]);
			/*
			  $sql[] = "BETWEEN DATE_FORMAT(" . $db->quote($this->value[0]) . ", '%m%d') AND " .
			  "DATE_FORMAT(" . $db->quote($this->value[1]) . ", '%m%d')";
			 */
		} else {
			if ($this->isDateType()) {
				$start = explode(' ', $this->value[0]);
				$end = explode(' ', $this->value[1]);
				if ($this->operator === 'between' && isset($start[1]) && isset($end[1])) {
					$this->value[0] = getValidDBInsertDateTimeValue($start[0] . ' ' . $start[1]);
					if ($start[0] === $end[0]) {
						$dateTime = new \DateTime($this->value[0]);
						$nextDay = strtotime($nextDay->modify('+1 days')->format('Y-m-d H:i:s')) - 1;
						$nextDay = date('Y-m-d H:i:s', $nextDay);
						$values = explode(' ', $nextDay);
						$this->value[1] = getValidDBInsertDateTimeValue($values[0]) . ' ' . $values[1];
					} else {
						$end = $this->value[1];
						$dateObject = new \DateTimeField($end);
						$this->value[1] = $dateObject->getDBInsertDateTimeValue();
					}
				} else {
					$this->value[0] = getValidDBInsertDateTimeValue($this->value[0]);
					$dateTimeStart = explode(' ', $this->value[0]);
					if ($dateTimeStart[1] === '00:00:00' && $this->operator !== 'between') {
						$this->value[0] = $dateTimeStart[0];
					}
					$this->value[1] = getValidDBInsertDateTimeValue($this->value[1]);
					$dateTimeEnd = explode(' ', $this->value[1]);
					if ($dateTimeEnd[1] === '00:00:00' || $dateTimeEnd[1] === '23:59:59') {
						$this->value[1] = $dateTimeEnd[0];
					}
				}
			}
			if ($this->operator === 'notequal') {
				$condition = ['not between', $this->fieldModel->getColumnName(), $this->value[0], $this->value[1]];
			} else {
				$condition = ['between', $this->fieldModel->getColumnName(), $this->value[0], $this->value[1]];
			}
		}
		return $condition;
	}
}
