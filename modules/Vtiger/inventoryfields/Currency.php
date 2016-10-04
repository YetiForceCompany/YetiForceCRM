<?php

/**
 * Inventory Currency Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Currency_InventoryField extends Vtiger_Basic_InventoryField
{

	protected $name = 'Currency';
	protected $defaultLabel = 'LBL_CURRENCY';
	protected $columnName = 'currency';
	protected $dbType = 'int(10)';
	protected $customColumn = [
		'currencyparam' => 'varchar(1024)'
	];
	protected $blocks = [0];

	/**
	 * Getting value to display
	 * @param int $value
	 * @return string
	 */
	public function getDisplayValue($value)
	{
		return vtlib\Functions::getCurrencyName($value, false);
	}

	public function getCurrencyParam($currencies, $param = false)
	{
		if ($param !== false) {
			return \includes\utils\Json::decode($param);
		} else {
			foreach ($currencies as $currency) {
				$return[$currency['id']] = vtlib\Functions::getConversionRateInfo($currency['id']);
			}
		}
		return $return;
	}
}
