<?php
namespace App\QueryField;

use App\Log;

/**
 * Base Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class BaseField
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
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var string|array 
	 */
	protected $value;

	/**
	 * @var string 
	 */
	protected $operator;

	/**
	 * @var array Releted detail 
	 */
	protected $releted = false;

	/**
	 * Constructor
	 * @param \App\QueryGenerator $queryGenerator
	 * @param \Vtiger_Field_Model $fieldModel
	 * @param string|array $value
	 * @param string $operator
	 */
	public function __construct(\App\QueryGenerator $queryGenerator, $fieldModel = false)
	{
		$this->queryGenerator = $queryGenerator;
		$this->fieldModel = $fieldModel;
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
	 * Set value
	 * @param string $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Set operator
	 * @param string $operator
	 */
	public function setOperator($operator)
	{
		$this->operator = strtolower($operator);
	}

	/**
	 * Set releted details
	 * @param array $reletedInfo
	 */
	public function setReleted($reletedInfo)
	{
		$this->releted = $reletedInfo;
	}

	/**
	 * Set table name
	 * @param string $tableName
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}

	/**
	 * Get table name
	 * @return string
	 */
	public function getTableName()
	{
		if ($this->tableName) {
			return $this->tableName;
		}
		$tableName = $this->fieldModel->getTableName();
		if ($this->releted) {
			$tableName .= $this->releted['sourceField'];
		}
		return $this->tableName = $tableName;
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
		return $this->fullColumnName = $this->getTableName() . '.' . $this->fieldModel->getColumnName();
	}

	/**
	 * Get order by
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		if ($order && strtoupper($order) === 'DESC') {
			return [$this->getColumnName() => SORT_DESC];
		} else {
			return [$this->getColumnName() => SORT_ASC];
		}
	}

	/**
	 * Get condition
	 * @return boolean|array
	 */
	public function getCondition()
	{
		$fn = 'operator' . ucfirst($this->operator);
		if (method_exists($this, $fn)) {
			Log::trace("Entering to $fn in " . __CLASS__);
			return $this->$fn();
		}
		Log::error("Not found operator: $fn in  " . __CLASS__);
		return false;
	}

	/**
	 * Auto operator, it allows you to use formulas: * and _
	 * @return array
	 */
	public function operatorA()
	{
		if (strpos($this->getValue(), '*') !== false) {
			return ['like', $this->getColumnName(), str_replace('*', '%', "%{$this->getValue()}%"), false];
		}
		if (strpos($this->getValue(), '_') !== false) {
			return ['like', $this->getColumnName(), "%{$this->getValue()}%", false];
		}
		return $this->operatorC();
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

	/**
	 * Contains operator
	 * @return array
	 */
	public function operatorC()
	{
		return ['like', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Does not contain operator
	 * @return array
	 */
	public function operatorK()
	{
		return ['not like', $this->getColumnName(), $this->getValue()];
	}
}
