<?php

/**
 * Inventory Item Number Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_ItemNumber_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'ItemNumber';
	protected $defaultLabel = 'LBL_ITEM_NUMBER';
	protected $columnName = 'seq';
	protected $purifyType = \App\Purifier::INTEGER;

	/**
	 * {@inheritdoc}
	 */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/ItemNumber.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, string $columnName, bool $isUserFormat)
	{
		if (filter_var($value, FILTER_VALIDATE_INT) === false) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}
}
