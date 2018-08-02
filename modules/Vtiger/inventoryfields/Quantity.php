<?php

/**
 * Inventory Quantity Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Quantity_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'Quantity';
	protected $defaultLabel = 'LBL_QUANTITY';
	protected $defaultValue = '1';
	protected $columnName = 'qty';
	protected $dbType = 'decimal(25,3) DEFAULT 0';
	protected $customColumn = [
		'qtyparam' => 'smallint(1) DEFAULT 0',
	];
	protected $maximumLength = '9999999999999999999999';
	protected $customMaximumLength = [
		'qtyparam' => '-32768,32767'
	];

	/**
	 * Getting value to display.
	 *
	 * @param type $value
	 *
	 * @return type
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return \App\Purifier::encodeHtml(vtlib\Functions::formatDecimal($value));
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
		$value = $request->getByType($column . $i, 'NumberInUserFormat');
		$this->validate($value, $column, true);
		$insertData[$column] = $value;
		$value = $request->isEmpty('qtyparam' . $i, true) ? 0 : $request->getInteger('qtyparam' . $i);
		$this->validate($value, $column, true);
		$insertData['qtyparam'] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if ($columnName === $this->getColumnName()) {
			$rangeValues = [$this->maximumLength];
		} else {
			$rangeValues = explode(',', $this->customMaximumLength[$columnName]);
		}
		if (($rangeValues[1] ?? $rangeValues[0]) < $value || (isset($rangeValues[1]) ? $rangeValues[0] : 0) > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}
}
