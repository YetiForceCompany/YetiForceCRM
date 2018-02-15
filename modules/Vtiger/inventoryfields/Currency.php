<?php

/**
 * Inventory Currency Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Currency_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'Currency';
	protected $defaultLabel = 'LBL_CURRENCY';
	protected $columnName = 'currency';
	protected $dbType = [\yii\db\Schema::TYPE_INTEGER, 11];
	protected $customColumn = [
		'currencyparam' => [\yii\db\Schema::TYPE_STRING, 1024],
	];
	protected $blocks = [0];

	/**
	 * Getting value to display.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return vtlib\Functions::getCurrencyName($value, false);
	}

	public function getCurrencyParam($currencies, $param = false)
	{
		if ($param !== false) {
			return \App\Json::decode($param);
		} else {
			foreach ($currencies as $currency) {
				$return[$currency['id']] = vtlib\Functions::getConversionRateInfo($currency['id']);
			}
		}

		return $return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueFromRequest(&$insertData, \App\Request $request, $i)
	{
		$column = $this->getColumnName();
		if (empty($column) || $column === '-' || !$request->has($column)) {
			return false;
		}
		$insertData[$column] = $request->getInteger($column);
		$insertData['currencyparam'] = \App\Json::encode($request->getArray('currencyparam'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}
}
