<?php

/**
 * Inventory Double Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Double_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Double';
	protected $defaultLabel = 'LBL_DOUBLE';
	protected $columnName = 'double';
	protected $dbType = [\yii\db\Schema::TYPE_DECIMAL, '28,8'];
	protected $onlyOne = false;
	protected $maximumLength = '99999999999999999999';
	protected $purifyType = \App\Purifier::NUMBER;

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
			$this->dbValue[$value] = App\Fields\Double::formatToDb($value);
		}
		return $this->dbValue[$value];
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, string $columnName, bool $isUserFormat)
	{
		if ($isUserFormat) {
			$value = $this->getDBValue($value, $columnName);
		}
		$moduleName = $this->getFieldModel()->getModuleName();
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$moduleName||$value", 406);
		} elseif ($this->maximumLength < $value || -$this->maximumLength > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$moduleName||$value", 406);
		}
	}
}
