<?php

/**
 * Inventory Integer Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Integer_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'Integer';
	protected $defaultLabel = 'LBL_INTEGER';
	protected $columnName = 'int';
	protected $dbType = [\yii\db\Schema::TYPE_INTEGER, 11];
	protected $onlyOne = false;
	protected $maximumLength = '99999999999999999999';

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (empty($value)) {
			return;
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if ($value > $this->maximumLength || $value < -$this->maximumLength) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value < $this->maximumLength", 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return \App\Fields\Integer::formatToDisplay($value);
	}
}
