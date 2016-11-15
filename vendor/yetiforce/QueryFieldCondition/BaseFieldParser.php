<?php
namespace App\QueryFieldCondition;

use App\Log;

/**
 * Base Query Condition Parser Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class BaseFieldParser
{

	/**
	 * @var QueryGenerator 
	 */
	protected $queryGenerator;

	/**
	 * @var \Vtiger_Field_Model 
	 */
	protected $fieldModel;

	/**
	 * @var string
	 */
	protected $fullColumnName;

	/**
	 * @var string|array 
	 */
	protected $value;

	/**
	 * @var string 
	 */
	protected $operator;

	const STRING_TYPE = ['string', 'text', 'email', 'reference'];
	const NUMERIC_TYPE = ['integer', 'double', 'currency'];
	const DATE_TYPE = ['date', 'datetime'];
	const EQUALITY_TYPES = ['currency', 'percentage', 'double', 'integer', 'number'];
	const COMMA_TYPES = ['picklist', 'multipicklist', 'owner', 'date', 'datetime', 'time', 'tree', 'sharedOwner', 'sharedOwner'];

	/**
	 * Constructor
	 * @param \App\QueryGenerator $queryGenerator
	 * @param \Vtiger_Field_Model $fieldModel
	 * @param string|array $value
	 * @param string $operator
	 */
	public function __construct(\App\QueryGenerator $queryGenerator, $fieldModel = false, $value, $operator)
	{
		$this->queryGenerator = $queryGenerator;
		$this->fieldModel = $fieldModel;
		$this->value = $value;
		$this->operator = strtolower($operator);
	}

	/**
	 * Get module name
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->queryGenerator->getModule();
	}

	/**
	 * Get value
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
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

	/**
	 * Get condition
	 * @return boolean|array
	 */
	public function getCondition()
	{
		$fn = 'operator' . ucfirst($this->operator);
		var_dump($fn);
		if (method_exists($this, $fn)) {
			Log::trace("Entering to $fn in " . __CLASS__);
			return $this->$fn();
		}
		Log::error("Not found operator: $fn in  " . __CLASS__);
		return false;
	}

	/**
	 * Equals operator
	 * @return array
	 */
	public function operatorE()
	{
		return [$this->getColumnName() => $this->getValue()];
	}

	/**
	 * Not equal operator
	 * @return array
	 */
	public function operatorN()
	{
		return ['<>', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Is empty operator
	 * @return array
	 */
	public function operatorY()
	{
		return ['or',
				[$this->getColumnName() => null],
				['=', $this->getColumnName(), '']
		];
	}

	/**
	 * Is not empty operator
	 * @return array
	 */
	public function operatorNy()
	{
		return ['and',
				['not', [$this->getColumnName() => null]],
				['<>', $this->getColumnName(), '']
		];
	}
}
