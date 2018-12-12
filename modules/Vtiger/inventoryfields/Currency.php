<?php

/**
 * Inventory Currency Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Currency_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Currency';
	protected $defaultLabel = 'LBL_CURRENCY';
	protected $columnName = 'currency';
	protected $dbType = [\yii\db\Schema::TYPE_INTEGER, 11];
	protected $customColumn = [
		'currencyparam' => [\yii\db\Schema::TYPE_STRING, 1024],
	];
	protected $blocks = [0];
	protected $maximumLength = '-2147483648,2147483647';
	protected $customMaximumLength = [
		'currencyparam' => 1024
	];
	protected $purifyType = \App\Purifier::INTEGER;
	protected $customPurifyType = [
		'currencyparam' => App\Purifier::TEXT
	];

	/**
	 * {@inheritdoc}
	 */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Currency.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		return \App\Fields\Currency::getById($value)['currency_name'];
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
		if (empty($column) || $column === '-' || !$request->has($column . $i)) {
			return false;
		}
		$value = $request->getInteger($column . $i);
		$this->validate($value, $column, true);
		$insertData[$column] = $value;
		$value = \App\Json::encode($request->getArray('currencyparam' . $i));
		$this->validate($value, 'currencyparam', true);
		$insertData['currencyparam'] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if ($columnName === $this->getColumnName()) {
			if (!is_numeric($value)) {
				throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
			}
			$rangeValues = explode(',', $this->maximumLength);
			if ($rangeValues[1] < $value || $rangeValues[0] > $value) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
		} else {
			if (App\TextParser::getTextLength($value) > $this->customMaximumLength[$columnName]) {
				throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
			}
		}
	}
}
