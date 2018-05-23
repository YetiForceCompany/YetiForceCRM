<?php

/**
 * Inventory DiscountMode Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_DiscountMode_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'DiscountMode';
	protected $defaultLabel = 'LBL_DISCOUNT_MODE';
	protected $defaultValue = '0';
	protected $columnName = 'discountmode';
	protected $dbType = 'smallint(1) DEFAULT 0';
	protected $values = [0 => 'group', 1 => 'individual'];
	protected $blocks = [0];
	protected $maximumLength = '-32768,32767';

	/**
	 * Getting value to display.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		if ($value === '') {
			return '';
		}

		return 'LBL_' . strtoupper($this->values[$value]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueFromRequest(&$insertData, \App\Request $request, $i)
	{
		$column = $this->getColumnName();
		if (empty($column) || $column === '-' || !$request->has($column)) {
			return false;
		}
		$value = $request->getInteger($column);
		$this->validate($value, $column, true);
		$insertData[$column] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		$rangeValues = explode(',', $this->maximumLength);
		if ($rangeValues[1] < $value || $rangeValues[0] > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}
}
