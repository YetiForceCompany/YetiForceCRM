<?php

/**
 * Inventory Integer Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Integer_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Integer';
	protected $defaultLabel = 'LBL_INTEGER';
	protected $columnName = 'int';
	protected $dbType = [\yii\db\Schema::TYPE_INTEGER, 11];
	protected $onlyOne = false;
	protected $maximumLength = '99999999999999999999';
	protected $purifyType = \App\Purifier::INTEGER;

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if (empty($value)) {
			return;
		}
		if (false === filter_var($value, FILTER_VALIDATE_INT)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if ($value > $this->maximumLength || $value < -$this->maximumLength) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value < $this->maximumLength", 406);
		}
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return \App\Fields\Integer::formatToDisplay($value);
	}
}
