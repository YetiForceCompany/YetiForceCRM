<?php

/**
 * Inventory Boolean Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Boolean_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Boolean';
	protected $defaultLabel = 'LBL_BOOLEAN';
	protected $columnName = 'bool';
	protected $dbType = \yii\db\Schema::TYPE_BOOLEAN;
	protected $onlyOne = false;
	protected $purifyType = \App\Purifier::BOOL;

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if (!\in_array($value, [0, 1, '1', '0', 'on'])) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}

	/** {@inheritdoc} */
	public function getEditValue($value)
	{
		return (bool) $value;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return (bool) $value ? App\Language::translate('LBL_YES') : App\Language::translate('LBL_NO');
	}
}
