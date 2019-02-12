<?php

/**
 * Inventory Discount Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Discount_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Discount';
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
	protected $purifyType = \App\Purifier::NUMBER;
	protected $customPurifyType = [
		'discountparam' => App\Purifier::TEXT
	];

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return \App\Fields\Double::formatToDisplay($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditValue($value)
	{
		return \App\Fields\Double::formatToDisplay($value, false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, ?string $name = '')
	{
		if (!isset($this->dbValue[$value])) {
			$this->dbValue[$value] = $name === $this->getColumnName() ? App\Fields\Double::formatToDb($value) : $value;
		}
		return $this->dbValue[$value];
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, string $columnName, bool $isUserFormat)
	{
		if ($columnName === $this->getColumnName()) {
			if ($isUserFormat) {
				$value = $this->getDBValue($value, $columnName);
			}
			if ($this->maximumLength < $value || -$this->maximumLength > $value) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
		} elseif (App\TextParser::getTextLength($value) > $this->customMaximumLength[$columnName]) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}
}
