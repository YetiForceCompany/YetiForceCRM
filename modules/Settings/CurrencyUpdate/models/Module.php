<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CurrencyUpdate_Module_Model extends \App\Base
{
	// Returns objects instance

	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Returns CRMS active currency name by currency code.
	 *
	 * @param mixed $code
	 *
	 * @return string - currency name
	 */
	public static function getCRMCurrencyName($code)
	{
		return (new \App\Db\Query())
			->select(['currency_name'])
			->from('vtiger_currencies')
			->where(['currency_code' => $code])
			->scalar();
	}

	/**
	 * Returns list of active currencies in CRM.
	 *
	 * @return <Integer> - number of currencies
	 */
	public function getCurrencyNum()
	{
		return \count(\App\Fields\Currency::getAll(true));
	}

	/**
	 * Returns currency exchange rates for systems active currencies from bank.
	 *
	 * @param string $dateCur - date for which to fetch exchange rates
	 * @param bool   $cron    - true if fired by server, and so updates systems currency conversion rates
	 *
	 * @return bool - true if fetched new exchange rates, false otherwise
	 */
	public function fetchCurrencyRates($dateCur, $cron = false): bool
	{
		if (!\App\RequestUtil::isNetConnection() || \count(($currencies = \App\Fields\Currency::getAll(true))) <= 1) {
			return false;
		}
		$notifyNewRates = false;

		$defaultId = \App\Fields\Currency::getDefault()['id'];
		unset($currencies[$defaultId]);
		$currIds = array_column($currencies, 'id', 'currency_code');

		$selectBankName = $this->getActiveBankName();
		$modelClassName = Vtiger_Loader::getComponentClassName('BankModel', $selectBankName, 'Settings:CurrencyUpdate');

		if (class_exists($modelClassName) && (new \App\Db\Query())->from('yetiforce_currencyupdate')
			->where(['exchange_date' => $dateCur, 'currency_id' => array_values($currIds), 'bank_id' => $this->getActiveBankId()])->count(1) !== \count($currIds)) {
			$bank = new $modelClassName();
			$bank->getRates($currIds, $dateCur, false);
			$notifyNewRates = true;
		}

		return $notifyNewRates;
	}

	// Synchronises database banks list with the bank classes existing on ftp

	public function refreshBanks()
	{
		$db = App\Db::getInstance();
		$dataReader = (new \App\Db\Query())->select(['id', 'bank_name'])
			->from('yetiforce_currencyupdate_banks')
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$id = $row['id'];
			$bankPath = ROOT_DIRECTORY . "/modules/Settings/CurrencyUpdate/bankmodels/{$row['bank_name']}.php";
			if (!file_exists($bankPath)) { // delete bank from database
				$db->createCommand()->delete('yetiforce_currencyupdate_banks', ['id' => $id])->execute();
			}
		}
		$dataReader->close();
		foreach (new DirectoryIterator(ROOT_DIRECTORY . '/modules/Settings/CurrencyUpdate/bankmodels/') as $fileInfo) {
			if (!$fileInfo->isFile() || 'php' !== $fileInfo->getExtension()) {
				continue;
			}
			$bankClassName = $fileInfo->getBasename('.php');
			$isExists = (new \App\Db\Query())->from('yetiforce_currencyupdate_banks')->where(['bank_name' => $bankClassName])->exists();
			if (!$isExists) {
				$db->createCommand()->insert('yetiforce_currencyupdate_banks', ['bank_name' => $bankClassName, 'active' => 0])->execute();
			}
		}
		\App\Cache::delete('ActiveBankForExchangeRate', '');
	}

	/**
	 * Update currency rate in archives.
	 *
	 * @param <Integer> $id       - exchange rate id
	 * @param <Float>   $exchange - exchange rate
	 */
	public function updateCurrencyRate($id, $exchange)
	{
		\App\Db::getInstance()->createCommand()
			->update('yetiforce_currencyupdate', ['exchange' => $exchange], ['id' => $id])
			->execute();
	}

	/**
	 * Adds currency exchange rate to archive.
	 *
	 * @param <Integer> $currId       - currency id
	 * @param <Date>    $exchangeDate - exchange date
	 * @param <Float>   $exchange     - exchange rate
	 * @param <Integer> $bankId       - bank id
	 */
	public function addCurrencyRate($currId, $exchangeDate, $exchange, $bankId)
	{
		\App\Db::getInstance()->createCommand()->insert('yetiforce_currencyupdate', [
			'currency_id' => $currId,
			'fetch_date' => date('Y-m-d'),
			'exchange_date' => $exchangeDate,
			'exchange' => $exchange,
			'bank_id' => $bankId,
		])->execute();
	}

	/**
	 * Returns currency exchange rate id.
	 *
	 * @param <Integer> $currencyId   - systems currency id
	 * @param <Date>    $exchangeDate - date of exchange rate
	 * @param <Integer> $bankId       - id of bank
	 *
	 * @return <Integer> - currency rate id
	 */
	public function getCurrencyRateId($currencyId, $exchangeDate, $bankId)
	{
		return (new \App\Db\Query())->select(['id'])
			->from('yetiforce_currencyupdate')
			->where(['exchange_date' => $exchangeDate, 'currency_id' => $currencyId, 'bank_id' => $bankId])
			->scalar();
	}

	/**
	 * Returns currency rates from archive.
	 *
	 * @param int    $bankId    - bank id
	 * @param string $dateStart - date
	 * @param string $dateEnd   - date
	 *
	 * @return <Array> - array containing currency rates
	 */
	public function getRatesHistory(int $bankId, string $dateStart, string $dateEnd)
	{
		$query = (new App\Db\Query())->select(['exchange', 'currency_name', 'currency_code', 'currency_symbol', 'fetch_date', 'exchange_date', 'currency_id'])
			->from('yetiforce_currencyupdate')
			->innerJoin('vtiger_currency_info', 'vtiger_currency_info.id = yetiforce_currencyupdate.currency_id')
			->innerJoin('yetiforce_currencyupdate_banks', 'yetiforce_currencyupdate_banks.id = yetiforce_currencyupdate.bank_id')
			->where(['yetiforce_currencyupdate.bank_id' => $bankId]);
		// filter by date - if not exists then display this months history
		if ($dateEnd) {
			$query->andWhere(['between', 'exchange_date', $dateStart, $dateEnd]);
		} else {
			$query->andWhere(['exchange_date' => $dateStart]);
		}
		return $query->orderBy(['exchange_date' => SORT_DESC, 'currency_id' => SORT_ASC])->all();
	}

	/**
	 * Returns list of supported currencies by active bank.
	 *
	 * @param string $bankName - bank name
	 *
	 * @return <Array> - array of supported currencies
	 */
	public function getSupportedCurrencies($bankName = null)
	{
		if (!\App\RequestUtil::isNetConnection() || (empty($bankName) && empty($this->getActiveBankName()))) {
			return [];
		}
		if (empty($bankName)) {
			$bankName = 'Settings_CurrencyUpdate_' . $this->getActiveBankName() . '_BankModel';
		}
		$currencies = [];
		try {
			$bank = new $bankName();
			$currencies = $bank->getSupportedCurrencies();
		} catch (\Throwable $ex) {
			\App\Log::error('Error during downloading table: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'CurrencyUpdate');
		}
		return $currencies;
	}

	/**
	 * Returns list of unsupported currencies by active bank.
	 *
	 * @param string $bankName - bank name
	 *
	 * @return <Array> - array of unsupported currencies
	 */
	public function getUnSupportedCurrencies($bankName = null)
	{
		if (!\App\RequestUtil::isNetConnection() || (empty($bankName) && empty($this->getActiveBankName()))) {
			return [];
		}
		if (empty($bankName)) {
			$bankName = 'Settings_CurrencyUpdate_' . $this->getActiveBankName() . '_BankModel';
		}
		$bank = new $bankName();
		$supported = $bank->getSupportedCurrencies();
		$dataReader = (new \App\Db\Query())->select(['currency_name', 'currency_code'])
			->from('vtiger_currency_info')
			->where(['currency_status' => 'Active', 'deleted' => 0])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$name = $row['currency_name'];
			$code = $row['currency_code'];
			$unsupported[$name] = $code;
		}
		$dataReader->close();
		return array_diff($unsupported, $supported);
	}

	/**
	 * Sets systems exchange rate for chosen currency.
	 *
	 * @param string  $currency - currency code
	 * @param <Float> $exchange - exchange rate
	 */
	public function setCRMConversionRate($currency, $exchange)
	{
		$rate = (float) $exchange;
		\App\Db::getInstance()->createCommand()
			->update('vtiger_currency_info', ['conversion_rate' => $rate], ['currency_code' => $currency])
			->execute();
	}

	/**
	 * Function that retrieves conversion rate from and to specified currency.
	 *
	 * @param int    $from - currency id
	 * @param int    $to   - currency id
	 * @param string $date - date of the exchange rate
	 *
	 * @return <Float> - conversion rate
	 */
	public function getCRMConversionRate(int $from, int $to, string $date)
	{
		$mainCurrencyCode = \App\Fields\Currency::getDefault()['id'];
		$exchange = 0;

		if ($to != $mainCurrencyCode && ($activeBankId = self::getActiveBankId())) {
			$exchange = (float) (\App\Fields\Currency::getCurrencyRatesFromArchive($date, $to, $activeBankId)['exchange'] ?? 0);
			if (empty($exchange) && \App\RequestUtil::isNetConnection()) {
				self::fetchCurrencyRates($date);
				$exchange = (float) (\App\Fields\Currency::getCurrencyRatesFromArchive($date, $to, $activeBankId)['exchange'] ?? 0);
			}
		}

		if ($exchange) {
			$exchange = 1 / $exchange;
		}

		if ($from != $mainCurrencyCode && ($activeBankId = self::getActiveBankId())) {
			$convertToMainCurrency = 0 == $exchange ? 1 : 1 / $exchange;

			$fromExchange = (float) (\App\Fields\Currency::getCurrencyRatesFromArchive($date, $from, $activeBankId)['exchange'] ?? 0);
			if (empty($fromExchange) && \App\RequestUtil::isNetConnection()) {
				self::fetchCurrencyRates($date);
				$fromExchange = (float) (\App\Fields\Currency::getCurrencyRatesFromArchive($date, $from, $activeBankId)['exchange'] ?? 0);
			}

			if ($to != $mainCurrencyCode) {
				$exchange = $fromExchange / $convertToMainCurrency;
			} else {
				$exchange = $fromExchange * $convertToMainCurrency;
			}
		}

		return $exchange = round($exchange, 5);
	}

	/**
	 * Returns id of active bank.
	 *
	 * @return <Integer> - bank id
	 */
	public function getActiveBankId()
	{
		return \App\Fields\Currency::getActiveBankForExchangeRateUpdate()['id'] ?? 0;
	}

	/**
	 * Saves new active bank by id.
	 *
	 * @param <Integer> $bankId - bank id
	 *
	 * @return bool - true on success or false
	 */
	public function setActiveBankById($bankId)
	{
		$db = \App\Db::getInstance();
		$result = $db->createCommand()->update('yetiforce_currencyupdate_banks', ['active' => 0])->execute();
		if (!empty($bankId)) {
			$result = $db->createCommand()->update('yetiforce_currencyupdate_banks', ['active' => 1], ['id' => $bankId])->execute();
		}
		\App\Cache::delete('ActiveBankForExchangeRate', '');

		return (bool) $result;
	}

	/**
	 * Returns active banks name.
	 *
	 * @return string - bank name
	 */
	public function getActiveBankName()
	{
		return \App\Fields\Currency::getActiveBankForExchangeRateUpdate()['bank_name'] ?? '';
	}
}
