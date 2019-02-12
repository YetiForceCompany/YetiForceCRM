<?php

/**
 * Inventory Date Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Date_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Date';
	protected $defaultLabel = 'LBL_DATE';
	protected $columnName = 'date';
	protected $dbType = \yii\db\Schema::TYPE_DATE;
	protected $onlyOne = false;
	protected $purifyType = \App\Purifier::DATE_USER_FORMAT;

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, ?string $name = '')
	{
		if (!isset($this->dbValue[$value])) {
			$this->dbValue[$value] = \App\Fields\Date::formatToDb($value);
		}
		return $this->dbValue[$value];
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, string $columnName, bool $isUserFormat)
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

	/**
	 * {@inheritdoc}
	 */
	public function getEditValue($value)
	{
		if (empty($value)) {
			return '';
		}
		return \App\Purifier::encodeHtml(DateTimeField::convertToUserFormat($value));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (empty($value)) {
			return '';
		} else {
			$dateValue = App\Fields\Date::formatToDisplay($value);
		}
		if ($dateValue === '--') {
			return '';
		} else {
			return $dateValue;
		}
	}
}
