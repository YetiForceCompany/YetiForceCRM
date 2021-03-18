<?php
/**
 * Base Query Field file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\QueryFields;

use App\Log;

/**
 * Base Query Field Class.
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
	 * @var array|string
	 */
	protected $value;

	/**
	 * @var string
	 */
	protected $operator;

	/**
	 * @var array Related detail
	 */
	protected $related = [];

	/**
	 * Constructor.
	 *
	 * @param \App\QueryGenerator $queryGenerator
	 * @param \Vtiger_Field_Model $fieldModel
	 * @param array|string        $value
	 * @param string              $operator
	 */
	public function __construct(\App\QueryGenerator $queryGenerator, $fieldModel = false)
	{
		$this->queryGenerator = $queryGenerator;
		$this->fieldModel = $fieldModel;
	}

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->queryGenerator->getModule();
	}

	/**
	 * Set operator.
	 *
	 * @param string $operator
	 */
	public function setOperator($operator)
	{
		$this->operator = strtolower($operator);
	}

	/**
	 * Set related details.
	 *
	 * @param array $relatedInfo
	 */
	public function setRelated($relatedInfo)
	{
		$this->related = $relatedInfo;
	}

	/**
	 * Get related details.
	 *
	 * @return array
	 */
	public function getRelated(): array
	{
		return $this->related;
	}

	/**
	 *  Get additional field model for list view.
	 *
	 * @return bool|\Vtiger_Field_Model
	 */
	public function getListViewFields()
	{
		return false;
	}

	/**
	 * Get order by.
	 *
	 * @param mixed $order
	 *
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		$order = $order && \App\Db::DESC === strtoupper($order) ? SORT_DESC : SORT_ASC;
		return [$this->getColumnName() => $order];
	}

	/**
	 * Get column name.
	 *
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
	 * Get table name.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		if ($this->tableName) {
			return $this->tableName;
		}
		$table = $this->fieldModel->getTableName();
		if ($this->related) {
			$table .= $this->related['sourceField'];
		}
		return $this->tableName = $table;
	}

	/**
	 * Set table name.
	 *
	 * @param string $tableName
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}

	/**
	 * Get condition.
	 *
	 * @param string|null $operator
	 *
	 * @return array|bool
	 */
	public function getCondition(?string $operator = null)
	{
		$fn = 'operator' . ucfirst($operator ?? $this->operator);
		if (method_exists($this, $fn)) {
			Log::trace("Entering to $fn in " . __CLASS__);
			return $this->{$fn}();
		}
		Log::error("Not found operator: $fn in  " . __CLASS__);
		return false;
	}

	/**
	 * Auto operator, it allows you to use formulas: * and _.
	 *
	 * @return array
	 */
	public function operatorA()
	{
		return $this->getCondition($this->getOperator());
	}

	/**
	 * Search for a specified pattern in a column.
	 * Wildcard - asterisk.
	 *
	 * @return array
	 */
	public function operatorWca()
	{
		return ['like', $this->getColumnName(), str_replace('*', '%', "%{$this->getValue()}%"), false];
	}

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Set value.
	 *
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Contains operator.
	 *
	 * @return array
	 */
	public function operatorC()
	{
		return ['like', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Equals operator.
	 *
	 * @return array
	 */
	public function operatorE()
	{
		return [$this->getColumnName() => $this->getValue()];
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN()
	{
		$value = $this->getValue();
		return [(\is_array($value) ? 'not in' : '<>'), $this->getColumnName(), $value];
	}

	/**
	 * Is empty operator.
	 *
	 * @return array
	 */
	public function operatorY()
	{
		return ['or',
			[$this->getColumnName() => null],
			['=', $this->getColumnName(), ''],
		];
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy()
	{
		return ['and',
			['not', [$this->getColumnName() => null]],
			['<>', $this->getColumnName(), ''],
		];
	}

	/**
	 * Does not contain operator.
	 *
	 * @return array
	 */
	public function operatorK()
	{
		return ['not like', $this->getColumnName(), $this->getValue()];
	}

	/**
	 * Get field model.
	 *
	 * @return \Vtiger_Field_Model
	 */
	public function getField()
	{
		return $this->fieldModel;
	}

	/**
	 * Invoked when object is cloning.
	 */
	public function __clone()
	{
		$this->fullColumnName = null;
		$this->tableName = null;
	}

	/**
	 * Gets real operator.
	 *
	 * @return string
	 */
	public function getOperator()
	{
		$operator = $this->operator;
		if ('a' === $this->operator) {
			$operator = 'c';
			if (false !== strpos($this->getValue(), '*')) {
				$operator = 'wca';
			}
		}
		return $operator;
	}
}
