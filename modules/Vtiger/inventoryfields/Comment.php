<?php

/**
 * Inventory Comment Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Comment_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'Comment';
	protected $defaultLabel = 'LBL_COMMENT';
	protected $colSpan = 0;
	protected $columnName = 'comment';
	protected $dbType = 'text';
	protected $onlyOne = false;
	protected $blocks = [2];

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return \App\Purifier::purifyHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueFromRequest(&$insertData, \App\Request $request, $i)
	{
		$column = $this->getColumnName();
		if (empty($column) || $column === '-' || !$request->has($column . $i)) {
			return false;
		}
		$value = $request->getForHtml($column . $i);
		$this->validate($value, $column, true);
		$insertData[$column] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (!is_string($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}
}
