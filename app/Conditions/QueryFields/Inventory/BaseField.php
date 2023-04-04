<?php
/**
 * Base query field conditions file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

declare(strict_types=1);

namespace App\Conditions\QueryFields\Inventory;

use App\Conditions\QueryFields;

/**
 * Base query field conditions class.
 */
class BaseField extends QueryFields\BaseField
{
	/** @var \Vtiger_Basic_InventoryField */
	protected $fieldModel;

	/**
	 * Constructor.
	 *
	 * @param \App\QueryGenerator          $queryGenerator
	 * @param \Vtiger_Basic_InventoryField $fieldModel
	 * @param array|string                 $value
	 * @param string                       $operator
	 */
	public function __construct(\App\QueryGenerator $queryGenerator, $fieldModel = null)
	{
		$this->queryGenerator = $queryGenerator;
		$this->fieldModel = $fieldModel;
	}

	/**
	 * Get column name.
	 *
	 * @return string
	 */
	public function getColumnName(): string
	{
		return $this->fullColumnName ? $this->fullColumnName : $this->fullColumnName = $this->getTableName() . '.' . $this->fieldModel->getColumnName();
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public function getTableName(): string
	{
		if (!$this->tableName) {
			$this->tableName = \Vtiger_Inventory_Model::getInstance($this->fieldModel->getModuleName())->getDataTableName();
		}

		return $this->tableName;
	}

	/**
	 * Get field model.
	 *
	 * @return \Vtiger_Basic_InventoryField
	 */
	public function getField()
	{
		return $this->fieldModel;
	}
}
