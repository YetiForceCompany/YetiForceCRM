<?php
namespace App;

/**
 * Currency class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Currency
{

	public static function getCurrencyIdByName($currencyName)
	{
		$currencyId = 1;
		$row = (new \App\Db\Query())->select(['id'])->from('vtiger_currency_info')->where(['currency_name' => $currencyName, 'deleted' => 0])->scalar();
		if ($row) {
			$currencyId = $row;
		}
		return $currencyId;
	}
}
