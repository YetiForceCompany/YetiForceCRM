<?php
/**
 * Tools for currency class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * Currency class.
 */
class Currency
{
	/**
	 * Function returns the currency in user specified format.
	 *
	 * @param string     $value          Date time
	 * @param mixed|null $user
	 * @param mixed      $skipConversion
	 * @param mixed      $skipFormatting
	 *
	 * @return string
	 */
	public static function formatToDisplay($value, $user = null, $skipConversion = false, $skipFormatting = false)
	{
		if (empty($value)) {
			return 0;
		}
		return \CurrencyField::convertToUserFormat($value, $user, $skipConversion, $skipFormatting);
	}

	/**
	 * Function to get value for db format.
	 *
	 * @param string $value
	 *
	 * @return float
	 */
	public static function formatToDb(string $value): ?float
	{
		if (empty($value)) {
			return 0;
		}
		$currentUser = \App\User::getCurrentUserModel();
		$value = str_replace([$currentUser->getDetail('currency_grouping_separator'), $currentUser->getDetail('currency_decimal_separator'), ' '], ['', '.', ''], $value);
		if (!\is_numeric($value)) {
			return null;
		}
		return $value;
	}

	/**
	 * Get currency by module name.
	 *
	 * @param bool|string $type
	 * @param mixed       $record
	 * @param mixed       $moduleName
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

	/**
	 * Get currency by code.
	 *
	 * @param string $code
	 * @param bool   $active
	 *
	 * @return int|null
	 */
	public static function getIdByCode(string $code, bool $active = true): ?int
	{
		return array_column(static::getAll($active), 'id', 'currency_code')[\strtoupper($code)] ?? null;
	}

	/**
	 * Get all currencies.
	 *
	 * @param bool $onlyActive
	 *
	 * @return array
	 */
	public static function getAll($onlyActive = false)
	{
		if (\App\Cache::has('CurrencyGetAll', 'All')) {
			$currencies = \App\Cache::get('CurrencyGetAll', 'All');
		} else {
			$currencies = (new \App\Db\Query())->from('vtiger_currency_info')->indexBy('id')->all();
			\App\Cache::save('CurrencyGetAll', 'All', $currencies);
		}
		if ($onlyActive) {
			foreach ($currencies as $id => $currency) {
				if ('Active' !== $currency['currency_status']) {
					unset($currencies[$id]);
				}
			}
		}
		return $currencies;
	}

	/**
	 * Get currency by id.
	 *
	 * @param int $currencyId
	 *
	 * @return array
	 */
	public static function getById(int $currencyId)
	{
		$currencyInfo = static::getAll();
		return $currencyInfo[$currencyId] ?? [];
	}

	/**
	 * Get current default currency data.
	 *
	 * @return array|bool
	 */
	public static function getDefault()
	{
		foreach (self::getAll(true) as $currency) {
			if (-11 === (int) $currency['defaultid']) {
				return $currency;
			}
		}
		return false;
	}
}
