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
	 * @var string
	 */
	private $fullColumnName;

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

	/**
	 * Get column name
	 * @return string
	 */
	public function getColumnName()
	{
		if ($this->fullColumnName) {
			return $this->fullColumnName;
		}
		return $this->fullColumnName = $this->fieldModel->getTableName() . '.' . $this->fieldModel->getColumnName();
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
		$valueSqlList = $this->parseConditionValue();
	}

	private function parseConditionValue()
	{
		if (is_string($this->value) && !$this->queryGenerator->getIgnoreComma()) {
			$this->parseListValue();
		} elseif ($this->operator === 'e' && $this->fieldModel->isReferenceField() && AppConfig::performance('SEARCH_REFERENCE_BY_AJAX')) {
			$this->value = explode(',', $value);
		} elseif (!is_array($this->value)) {
			$this->value = [$this->value];
		}
		if ($this->operator === 'between' || $this->operator === 'bw' || $this->operator === 'notequal') {
			return $this->parseBetweenValue();
		}
		return $this->getByFieldType();
		//return $this->parseValues();
	}

	private function parseListValue()
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

	private function parseValues()
	{
		$condition = [];
		foreach ($this->value as &$value) {
			if (!$this->isStringType()) {
				$value = trim($value);
			}
			if ($this->operator === 'empty' || $this->operator === 'y') {
				$condition[] = ['or', [$this->getColumnName() => null], [$this->getColumnName() => '']];
				//$condition[] = sprintf("IS NULL || %s = ''", $this->getColumnName());
				continue;
			}
			if ($this->operator === 'ny') {
				$condition[] = ['or', [$this->getColumnName() => null], [$this->getColumnName() => '']];
				$sql[] = sprintf("IS NOT NULL && %s != ''", $this->getSQLColumn($this->fieldModel->getFieldName()));
				continue;
			}
			if ((strtolower(trim($value)) === 'null') || (trim($value) === '' && !$this->isStringType()) && ($this->operator === 'e' || $this->operator === 'n')) {
				if ($this->operator === 'e') {
					$sql[] = "IS NULL";
					continue;
				}
				$sql[] = "IS NOT NULL";
				continue;
			} elseif ($this->fieldModel->getFieldDataType() == 'boolean') {
				$value = strtolower($value);
				if ($value == 'yes') {
					$value = 1;
				} elseif ($value == 'no') {
					$value = 0;
				}
			} elseif ($this->isDateType($this->fieldModel->getFieldDataType())) {
				// For "after" and "before" conditions
				$values = explode(' ', $value);
				if (($this->operator == 'a' || $this->operator == 'b') && count($values) == 2) {
					if ($this->operator == 'a') {
						// for after comparator we should check the date after the given
						$dateTime = new DateTime($value);
						$modifiedDate = $dateTime->modify('+1 days');
						$nextday = $modifiedDate->format('Y-m-d H:i:s');
						$temp = strtotime($nextday) - 1;
						$date = date('Y-m-d H:i:s', $temp);
						$value = getValidDBInsertDateTimeValue($date);
					} else {
						$dateTime = new DateTime($value);
						$prevday = $dateTime->format('Y-m-d H:i:s');
						$temp = strtotime($prevday) - 1;
						$date = date('Y-m-d H:i:s', $temp);
						$value = getValidDBInsertDateTimeValue($date);
					}
				} else {
					$value = getValidDBInsertDateTimeValue($value);
					$dateTime = explode(' ', $value);
					if ($dateTime[1] == '00:00:00') {
						$value = $dateTime[0];
					}
				}
			} elseif ($this->isEqualityType()) {
				$table = get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, vglobal('default_charset'));
				$chars = implode('', array_keys($table));
				if (preg_match("/[{$chars}]+/", $value) === 1) {
					if ($this->operator == 'g' || $this->operator == 'l') {
						$value = substr($value, 4);
					} elseif ($this->operator == 'h' || $this->operator == 'm') {
						$value = substr($value, 5);
					}
				}
			} elseif ($this->fieldModel->getFieldDataType() === 'currency') {
				$uiType = $this->fieldModel->getUIType();
				if ($uiType == 72) {
					$value = CurrencyField::convertToDBFormat($value, null, true);
				} elseif ($uiType == 71) {
					$value = CurrencyField::convertToDBFormat($value);
				}
			}

			if ($this->fieldModel->getFieldName() === 'birthday' && !$this->isRelativeSearchOperators($this->operator)) {
				$value = "DATE_FORMAT(" . $db->quote($value) . ", '%m%d')";
			} else {
				//$value = $db->sql_escape_string($value, true);
			}

			if ($this->fieldModel->getFieldDataType() === 'multiReferenceValue' && in_array($this->operator, ['e', 's', 'ew', 'c'])) {
				$sql[] = "LIKE '%$value%'";
				continue;
			} elseif ($this->fieldModel->getFieldDataType() === 'multiReferenceValue' && in_array($this->operator, ['n', 'k'])) {
				$sql[] = "NOT LIKE '%$value%'";
				continue;
			}

			if (trim($value) === '' && ($this->operator === 's' || $this->operator === 'ew' || $this->operator === 'c') && ($this->isStringType() ||
				$this->fieldModel->getFieldDataType() === 'picklist' ||
				$this->fieldModel->getFieldDataType() === 'multipicklist')) {
				$sql[] = "LIKE ''";
				continue;
			}
			if (trim($value) === '' && ($this->operator == 'om') && in_array($this->fieldModel->getFieldName(), $this->ownerFields)) {
				$sql[] = " = '" . $this->user->id . "'";
				continue;
			}
			if (trim($value) === '' && in_array($this->operator, ['wr', 'nwr']) && in_array($this->fieldModel->getFieldName(), $this->ownerFields)) {
				$userId = $this->user->id;
				$watchingSql = '((SELECT COUNT(*) FROM u_yf_watchdog_module WHERE userid = ' . $userId . ' && module = ' . vtlib\Functions::getModuleId($this->module) . ') > 0 && ';
				$watchingSql .= '(SELECT COUNT(*) FROM u_yf_watchdog_record WHERE userid = ' . $userId . ' && record = vtiger_crmentity.crmid && state = 0) = 0) || ';
				$watchingSql .= '((SELECT COUNT(*) FROM u_yf_watchdog_module WHERE userid = ' . $userId . ' && module = ' . vtlib\Functions::getModuleId($this->module) . ') = 0 && ';
				$watchingSql .= '(SELECT COUNT(*) FROM u_yf_watchdog_record WHERE userid = ' . $userId . ' && record = vtiger_crmentity.crmid && state = 1) > 0)';
				$sql[] = $watchingSql;
				continue;
			}
			if ($this->fieldModel->getUIType() === 120) {
				if ($this->operator == 'om') {
					$sql[] = 'vtiger_crmentity.crmid IN (SELECT DISTINCT crmid FROM u_yf_crmentity_showners WHERE userid = ' . $this->user->id . ')';
				} elseif (in_array($this->operator, ['e', 's', 'ew', 'c'])) {
					$sql[] = 'vtiger_crmentity.crmid IN (SELECT DISTINCT crmid FROM u_yf_crmentity_showners WHERE userid = ' . $value . ')';
				} elseif (in_array($this->operator, ['n', 'k'])) {
					$sql[] = 'vtiger_crmentity.crmid NOT IN (SELECT DISTINCT crmid FROM u_yf_crmentity_showners WHERE userid = ' . $value . ')';
				}
				continue;
			}
			if ($this->fieldModel->getUIType() === 307) {
				if ($value == '-') {
					$sql[] = 'IS NULL';
					continue;
				} elseif (!in_array(substr($value, 0, 1), ['>', '<']) && !in_array(substr($value, 0, 2), ['>=', '<=']) && !is_numeric($value)) {
					$value = "'$value'";
				}
			}
			if (trim($value) == '' && ($this->operator == 'k') &&
				$this->isStringType($this->fieldModel->getFieldDataType())) {
				$sql[] = "NOT LIKE ''";
				continue;
			}
			//$sqlOperatorData = $this->getSqlOperator($this->operator, $value);
			//$sqlOperator = $sqlOperatorData[0];
			//$value = $sqlOperatorData[1];

			if (!$this->isNumericType($this->fieldModel->getFieldDataType()) &&
				($this->fieldModel->getFieldName() != 'birthday' || ($this->fieldModel->getFieldName() == 'birthday' && $this->isRelativeSearchOperators($this->operator)))) {
				$value = "'$value'";
			}
			if ($this->isNumericType($this->fieldModel->getFieldDataType()) && empty($value)) {
				$value = '0';
			}
			$sql[] = "$sqlOperator $value";
		}
		return $condition;
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

	private function getByFieldType()
	{
		$type = 'get' . ucfirst($this->fieldModel->getFieldDataType()) . 'Type';
		return $this->$type();
	}

	private function getPicklistType()
	{
		var_dump($this->value);
	}

	private function getStringType()
	{
		switch ($variable) {
			case $value:


				break;

			default:
				break;
		}
	}
}
