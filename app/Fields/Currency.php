<?php
/**
 * Tools for currency class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * @param int    $record
	 * @param string $moduleName
	 *
	 * @return int
	 */
	public static function getCurrencyByModule(int $record, string $moduleName)
	{
		return \Vtiger_Record_Model::getInstanceById($record, $moduleName)->get('currency_id');
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
			$currencies = (new \App\Db\Query())->from('vtiger_currency_info')->where(['deleted' => 0])->indexBy('id')->all();
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
	 * Get supported currencies.
	 *
	 * @return array
	 */
	public static function getSupported(): array
	{
		if (\App\Cache::has('CurrencySupported', 'All')) {
			$currencies = \App\Cache::get('CurrencySupported', 'All');
		} else {
			$currencies = (new \App\Db\Query())->from('vtiger_currencies')->indexBy('currency_code')->all();
			\App\Cache::save('CurrencySupported', 'All', $currencies);
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

	/**
	 * Function clears cache.
	 *
	 * @return void
	 */
	public static function clearCache(): void
	{
		\App\Cache::delete('CurrencyGetAll', 'All');
		\App\Cache::delete('CurrencySupported', 'All');
	}

	/**
	 * Add the currency by code.
	 *
	 * @param string $code
	 *
	 * @return int|null
	 */
	public static function addCurrency(string $code): ?int
	{
		$supported = self::getSupported();
		if (empty($supported[$code])) {
			\App\Log::error('No currency code to add found: ' . $code);
			return null;
		}
		$db = \App\Db::getInstance();
		$db->createCommand()
			->insert('vtiger_currency_info', [
				'currency_name' => $supported[$code]['currency_name'],
				'currency_code' => $code,
				'currency_symbol' => $supported[$code]['currency_symbol'],
				'conversion_rate' => 1,
				'currency_status' => 'Active',
			])->execute();
		self::clearCache();
		return $db->getLastInsertID('vtiger_currency_info_id_seq');
	}
}
