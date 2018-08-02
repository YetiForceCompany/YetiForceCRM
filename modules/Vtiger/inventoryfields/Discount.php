<?php

/**
 * Inventory Discount Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Discount_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'Discount';
	protected $defaultLabel = 'LBL_DISCOUNT';
	protected $defaultValue = 0;
	protected $columnName = 'discount';
	protected $dbType = 'decimal(28,8) DEFAULT 0';
	protected $customColumn = [
		'discountparam' => 'string',
	];
	protected $summationValue = true;
	protected $maximumLength = '99999999999999999999';
	protected $customMaximumLength = [
		'discountparam' => 255
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
		return CurrencyField::convertToUserFormat($value, null, true);
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
		$value = \App\Json::encode($request->getArray('discountparam' . $i));
		$this->validate($value, 'discountparam', true);
		$insertData['discountparam'] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if ($columnName === $this->getColumnName()) {
			if ($this->maximumLength < $value || -$this->maximumLength > $value) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
		} else {
			if (App\TextParser::getTextLength($value) > $this->customMaximumLength[$columnName]) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
		}
	}
}
