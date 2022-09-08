<?php

/**
 * Inventory Date Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Date_InventoryField extends Vtiger_Basic_InventoryField
{
	/** {@inheritdoc} */
	protected $type = 'Date';
	/** {@inheritdoc} */
	protected $defaultLabel = 'LBL_DATE';
	/** {@inheritdoc} */
	protected $columnName = 'date';
	/** {@inheritdoc} */
	protected $dbType = \yii\db\Schema::TYPE_DATE;
	/** {@inheritdoc} */
	protected $onlyOne = false;
	/** {@inheritdoc} */
	protected $purifyType = \App\Purifier::DATE_USER_FORMAT;

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		if (!isset($this->dbValue[$value])) {
			$this->dbValue[$value] = empty($value) ? '' : \App\Fields\Date::formatToDb($value);
		}
		return $this->dbValue[$value];
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if ($value) {
			if ($isUserFormat) {
				$value = $this->getDBValue($value, $columnName);
			}
			[$y, $m, $d] = explode('-', $value);
			if (!is_numeric($m) || !is_numeric($d) || !is_numeric($y) || !checkdate($m, $d, $y)) {
				throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
			}
		}
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return !empty($value) && preg_replace('/[\D]/', '', $dateValue = App\Fields\Date::formatToDisplay($value)) ? $dateValue : '';
	}
}
