<?php
/**
 * Tools for currency class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * Currency class.
 */
class Currency
{
	/**
	 * Get currency by module name.
	 *
	 * @param bool|string $type
	 *
	 * @return array
	 */
	public static function getCurrencyByModule($record, $moduleName)
	{
		$cacheKey = "$record|$moduleName";
		if (\App\Cache::has('Currency|getCurrencyByModule', $cacheKey)) {
			return \App\Cache::get('Currency|getCurrencyByModule', $cacheKey);
		}
		$instance = \CRMEntity::getInstance($moduleName);
		$currencyId = (new \App\Db\Query())->select(['currency_id'])->from($instance->table_name)->where([$instance->table_index => $record])->scalar();
		\App\Cache::save('Currency|getCurrencyByModule', $cacheKey, $currencyId);

		return $currencyId;
	}

	/**
	 * Get currency id by name.
	 *
	 * @param type $currencyName
	 *
	 * @return type
	 */
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
