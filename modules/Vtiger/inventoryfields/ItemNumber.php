<?php

/**
 * Inventory Item Number Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_ItemNumber_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'ItemNumber';
	protected $defaultLabel = 'LBL_ITEM_NUMBER';
	protected $columnName = 'seq';
	protected $purifyType = \App\Purifier::INTEGER;

	/** {@inheritdoc} */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/ItemNumber.tpl';
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if ($value && $value !== filter_var($value, FILTER_VALIDATE_INT)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $columnName . '||' . $this->getModuleName() . '||' . $value, 406);
		}
	}
}
