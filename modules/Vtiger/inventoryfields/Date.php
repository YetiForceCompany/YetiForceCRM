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
	public function getValueFromRequest(&$insertData, \App\Request $request, $i)
	{
		$column = $this->getColumnName();
		if (empty($column) || $column === '-' || !$request->has($column . $i)) {
			return false;
		}
		$value = $request->getByType($column . $i, 'DateInUserFormat');
		$this->validate($value, $column, true);
		$insertData[$column] = $value ? \App\Fields\Date::formatToDb($value) : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (empty($value)) {
			return '';
		}
		if ($isUserFormat) {
			list($y, $m, $d) = App\Fields\Date::explode($value, App\User::getCurrentUserModel()->getDetail('date_format'));
		} else {
			list($y, $m, $d) = explode('-', $value);
		}

		if (!checkdate($m, $d, $y)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
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
	public function getDisplayValue($value, $rawText = false)
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
