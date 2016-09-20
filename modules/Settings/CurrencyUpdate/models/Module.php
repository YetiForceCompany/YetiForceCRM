<?php

/**
 * @package YetiForce.models
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CurrencyUpdate_Module_Model extends Vtiger_Base_Model
{
	/*
	 * Returns objects instance
	 */

	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}
	/*
	 * Returns CRMS active currency name by currency code
	 * @return <String> - currency name
	 */

	public static function getCRMCurrencyName($code)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `currency_name` FROM `vtiger_currencies` WHERE `currency_code` = ? LIMIT 1;';
		$result = $db->pquery($query, [$code]);

		return $db->getSingleValue($result);
	}
	/*
	 * Returns list of active currencies in CRM
	 * @return <Integer> - number of currencies
	 */

	public function getCurrencyNum()
	{
		return count(vtlib\Functions::getAllCurrency(true));
	}
	/*
	 * Returns currency exchange rates for systems active currencies from bank
	 * @param <Date> $date - date for which to fetch exchange rates
	 * @param <Boolean> $cron - true if fired by server, and so updates systems currency conversion rates
	 * @return <Boolean> - true if fetched new exchange rates, false otherwise
	 */

	public function fetchCurrencyRates($dateCur, $cron = false)
	{

		$db = PearDatabase::getInstance();
		$notifyNewRates = false;
		$vtigerCurrencySql = 'SELECT `id`, `currency_code` FROM `vtiger_currency_info` WHERE `currency_status` = ? && `deleted` = 0 && `defaultid` != ?;';
		$vtigerCurrencyResult = $db->pquery($vtigerCurrencySql, ['Active', '-11']);
		$numToConvert = $db->num_rows($vtigerCurrencyResult);

		if ($numToConvert >= 1) {
			$selectBankId = $this->getActiveBankId();
			$activeBankName = 'Settings_CurrencyUpdate_models_' . $this->getActiveBankName() . '_BankModel';
			$currIds = [];
			$otherCurrencyCode = [];
			while ($row = $db->fetchByAssoc($vtigerCurrencyResult)) {
				$id = $row['id'];
				$code = $row['currency_code'];
				$currIds[] = $id;
				$otherCurrencyCode[$code] = $id;
			}

			$existSql = sprintf('SELECT COUNT(*) as num FROM `yetiforce_currencyupdate` WHERE `exchange_date` = ? && `currency_id` IN (%s) && `bank_id` = ? LIMIT 1;', $db->generateQuestionMarks($currIds));
			$params = [$dateCur];
			$params = array_merge($params, $currIds);
			$params[] = $selectBankId;
			$existResult = $db->pquery($existSql, $params);

			$currNum = $db->getSingleValue($existResult);
			// download only if its not in archives
			if ($currNum != $numToConvert && class_exists($activeBankName)) {
				$bank = new $activeBankName();
				$bank->getRates($otherCurrencyCode, $dateCur, false);
				$notifyNewRates = true;
			}
		}

		return $notifyNewRates;
	}
	/*
	 * Synchronises database banks list with the bank classes existing on ftp
	 */

	public function refreshBanks()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `id`, `bank_name` FROM `yetiforce_currencyupdate_banks`;';
		$result = $db->query($query);

		while ($row = $db->fetchByAssoc($result)) {
			$id = $row['id'];
			$bankName = $row['bank_name'];

			$bankPath = __DIR__ . '/bankmodels/' . $bankName . '.php';
			if (!file_exists($bankPath)) { // delete bank from database
				$query = 'DELETE FROM `yetiforce_currencyupdate_banks` WHERE `id` = ? LIMIT 1;';
				$db->pquery($query, [$id]);
			}
		}

		foreach (new DirectoryIterator(__DIR__ . '/bankmodels/') as $fileInfo) {
			$fileName = $fileInfo->getFilename();
			$extension = end(explode('.', $fileName));
			$bankClassName = basename($fileName, '.' . $extension);
			if ($fileInfo->isDot() || $extension != 'php') {
				continue;
			}

			$query = 'SELECT COUNT(*) as num FROM `yetiforce_currencyupdate_banks` WHERE `bank_name` = ?;';
			$result = $db->pquery($query, [$bankClassName]);
			$bankExists = intval($db->getSingleValue($result));

			if (!$bankExists) {
				$query = 'INSERT INTO `yetiforce_currencyupdate_banks` (`bank_name`, `active`) VALUES (?,?);';
				$db->pquery($query, [$bankClassName, 0]);
			}
		}

		$activeId = $this->getActiveBankId();

		if (!$activeId) {
			$query = 'UPDATE `yetiforce_currencyupdate_banks` SET `active` = 1 ORDER BY `id` ASC LIMIT 1;';
			$db->query($query);
		}
	}
	/*
	 * Update currency rate in archives
	 * @param <Integer> $id - exchange rate id
	 * @param <Float> $exchange - exchange rate
	 */

	public function updateCurrencyRate($id, $exchange)
	{
		$db = PearDatabase::getInstance();

		$query = 'UPDATE `yetiforce_currencyupdate` SET `exchange` = ? WHERE `id` = ? LIMIT 1;';
		$db->pquery($query, [$exchange, $id]);
	}
	/*
	 * Adds currency exchange rate to archive
	 * @param <Integer> $currId - currency id
	 * @param <Date> $exchangeDate - exchange date
	 * @param <Float> $exchange - exchange rate
	 * @param <Integer> $bankId - bank id
	 */

	public function addCurrencyRate($currId, $exchangeDate, $exchange, $bankId)
	{
		$db = PearDatabase::getInstance();
		$db->insert('yetiforce_currencyupdate', [
			'currency_id' => $currId,
			'fetch_date' => date('Y-m-d'),
			'exchange_date' => $exchangeDate,
			'exchange' => $exchange,
			'bank_id' => $bankId,
		]);
	}
	/*
	 * Returns currency exchange rate id
	 * @param <Integer> $currencyId - systems currency id
	 * @param <Date> $exchangeDate - date of exchange rate
	 * @param <Integer> $bankId - id of bank
	 * @return <Integer> - currency rate id
	 */

	public function getCurrencyRateId($currencyId, $exchangeDate, $bankId)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `id` FROM `yetiforce_currencyupdate` WHERE  `exchange_date` = ? && `currency_id` = ? && `bank_id` = ? LIMIT 1;';
		$params = [$exchangeDate, $currencyId, $bankId];
		$result = $db->pquery($query, $params);

		return intval($db->getSingleValue($result));
	}
	/*
	 * Returns currency rates from archive
	 * @param <Integer> $bankId - bank id
	 * @param <Date> $dateCur - date, if empty show this months history
	 * @return <Array> - array containing currency rates
	 */

	public function getRatesHistory($bankId, $dateCur, $request)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT 
					`exchange`, 
					`currency_name`, 
					`currency_code`, 
					`currency_symbol`, 
					`fetch_date`, 
					`exchange_date` 
				FROM 
					`yetiforce_currencyupdate` 
					INNER JOIN `vtiger_currency_info` 
						ON `yetiforce_currencyupdate`.`currency_id` = `vtiger_currency_info`.`id` 
					INNER JOIN `yetiforce_currencyupdate_banks` 
						ON `yetiforce_currencyupdate_banks`.`id` = `yetiforce_currencyupdate`.`bank_id` 
				WHERE 
					`yetiforce_currencyupdate`.`bank_id` = ? ';
		$params = [$bankId];

		// filter by date - if not exists then display this months history
		$filter = $request->get('duedate');
		if ($filter == '' && $dateCur) {
			$query .= 'AND `exchange_date` BETWEEN ? AND ? ';
			$params[] = date('Y-m-01');
			$params[] = date('Y-m-t');
		} else {
			$query .= 'AND `exchange_date` = ? ';
			$params[] = $dateCur;
		}

		$query .= 'ORDER BY `exchange_date` DESC, `currency_code` ASC;';

		$result = $db->pquery($query, $params);

		$history = array();

		$i = 0;
		while ($row = $db->fetchByAssoc($result)) {
			$history[$i]['exchange'] = $row['exchange'];
			$history[$i]['currency_name'] = $row['currency_name'];
			$history[$i]['code'] = $row['currency_code'];
			$history[$i]['symbol'] = $row['currency_symbol'];
			$history[$i]['fetch_date'] = $row['fetch_date'];
			$history[$i]['exchange_date'] = $row['exchange_date'];
			$i++;
		}

		return $history;
	}
	/*
	 * Returns list of supported currencies by active bank
	 * @param <String> $bankName - bank name
	 * @return <Array> - array of supported currencies
	 */

	public function getSupportedCurrencies($bankName = null)
	{
		if (!$bankName) {
			$bankName = 'Settings_CurrencyUpdate_models_' . $this->getActiveBankName() . '_BankModel';
		}
		$bank = new $bankName();

		return $bank->getSupportedCurrencies();
	}
	/*
	 * Returns list of unsupported currencies by active bank
	 * @param <String> $bankName - bank name
	 * @return <Array> - array of unsupported currencies
	 */

	public function getUnSupportedCurrencies($bankName = null)
	{
		if (!$bankName) {
			$bankName = 'Settings_CurrencyUpdate_models_' . $this->getActiveBankName() . '_BankModel';
		}
		$bank = new $bankName();

		$supported = $bank->getSupportedCurrencies($bankName);
		$db = PearDatabase::getInstance();

		$query = 'SELECT `currency_name`, `currency_code` FROM vtiger_currency_info WHERE `currency_status` = "Active" && `deleted` = 0;';
		$result = $db->query($query);

		$unsupported = [];
		while ($row = $db->fetchByAssoc($result)) {
			$name = $row['currency_name'];
			$code = $row['currency_code'];

			$unsupported[$name] = $code;
		}

		return array_diff($unsupported, $supported);
	}
	/*
	 * Sets systems exchange rate for chosen currency
	 * @param <String> $currency - currency code
	 * @param <Float> $exchange - exchange rate
	 */

	public function setCRMConversionRate($currency, $exchange)
	{
		$db = PearDatabase::getInstance();

		$rate = (float) $exchange;

		$query = 'UPDATE `vtiger_currency_info` SET `conversion_rate` = ? WHERE `currency_code` = ? LIMIT 1;';
		$db->pquery($query, [$rate, $currency]);
	}
	/*
	 * Function that retrieves conversion rate from and to specified currency
	 * @param <String> $from - currency code or id (converted to code)
	 * @param <String> $to - currency code or id (converted to code)
	 * @param <Date> $date - date of the exchange rate
	 * @return <Float> - conversion rate
	 */

	public function getCRMConversionRate($from, $to, $date = '')
	{
		$db = PearDatabase::getInstance();
		$mainCurrencyCode = vtlib\Functions::getDefaultCurrencyInfo()['currency_code'];
		$activeBankId = self::getActiveBankId();
		$exchange = false;
		if (is_numeric($from)) {
			$from = vtlib\Functions::getAllCurrency(true)[$from]['currency_code'];
		}
		if (is_numeric($to)) {
			$to = vtlib\Functions::getAllCurrency(true)[$to]['currency_code'];
		}
		// get present conversion rate from crm
		if (empty($date)) {
			$query = 'SELECT `conversion_rate` FROM `vtiger_currency_info` WHERE `currency_code` = ? LIMIT 1;';
			$result = $db->pquery($query, [$to]);
			$exchange = floatval($db->getSingleValue($result));

			if ($from != $mainCurrencyCode) {
				$convertToMainCurrency = 1 / $exchange;
				$query = 'SELECT `conversion_rate` FROM `vtiger_currency_info` WHERE `currency_code` = ? LIMIT 1;';
				$result = $db->pquery($query, [$from]);
				$fromExchange = floatval($db->getSingleValue($result));

				$exchange = 1 / ($fromExchange * $convertToMainCurrency);
			}
		}
		// get conversion rate from archive
		else {
			$query = 'SELECT 
						COUNT(1) as num  
					FROM 
						`yetiforce_currencyupdate` yfc 
						INNER JOIN `vtiger_currency_info` cur 
							ON yfc.`currency_id` = cur.`id` && cur.`deleted` = 0 
					WHERE 
						yfc.`exchange_date` = ? && 
						yfc.`bank_id` = ? && 
						cur.`currency_code` = ? 
					LIMIT 1;';
			$result = $db->pquery($query, [$date, $activeBankId, $to]);
			$num = floatval($db->getSingleValue($result));

			// no exchange rate in archive, fetch new rates
			if ($num == 0) {
				self::fetchCurrencyRates($date);
			}
			$query = 'SELECT 
						yfc.`exchange` 
					FROM 
						`yetiforce_currencyupdate` yfc 
						INNER JOIN `vtiger_currency_info` cur 
							ON yfc.`currency_id` = cur.`id` && cur.`deleted` = 0 
					WHERE 
						yfc.`exchange_date` = ? && 
						yfc.`bank_id` = ? && 
						cur.`currency_code` = ? 
					LIMIT 1;';
			$result = $db->pquery($query, [$date, $activeBankId, $to]);
			$exchange = floatval($db->getSingleValue($result));
			if ($exchange > 0) {
				$exchange = 1 / $exchange;
			}

			if ($from != $mainCurrencyCode) {
				$convertToMainCurrency = $exchange == 0 ? 1 : 1 / $exchange;
				$query = 'SELECT 
							yfc.`exchange` 
						FROM 
							`yetiforce_currencyupdate` yfc 
							INNER JOIN `vtiger_currency_info` cur 
								ON yfc.`currency_id` = cur.`id` && cur.`deleted` = 0 
						WHERE 
							yfc.`exchange_date` = ? && 
							yfc.`bank_id` = ? && 
							cur.`currency_code` = ? 
						LIMIT 1;';
				$result = $db->pquery($query, [$date, $activeBankId, $from]);
				$fromExchange = floatval($db->getSingleValue($result));
				if ($from != $mainCurrencyCode && $to != $mainCurrencyCode) {
					$exchange = $fromExchange / $convertToMainCurrency;
				} else {
					$exchange = $fromExchange * $convertToMainCurrency;
				}
			}
		}

		return $exchange = round($exchange, 5);
	}
	/*
	 * Convert given amount in one currency to another
	 * @param <Float> $amount - number to convert
	 * @param <String> $from - currency code
	 * @param <String> $to - currency code
	 * @param <Date> $date - date of the exchange rate
	 * @return <Float> - floating point number
	 */

	public function convertFromTo($amount, $from, $to, $date = false)
	{
		return round($amount * $this->getCRMConversionRate($from, $to, $date), 5);
	}
	/*
	 * Returns id of active bank
	 * @return <Integer> - bank id
	 */

	public function getActiveBankId()
	{
		$db = PearDatabase::getInstance();

		$queryBank = 'SELECT `id` FROM `yetiforce_currencyupdate_banks` WHERE `active` = 1 LIMIT 1;';
		$resultBank = $db->query($queryBank);

		$bankInfo = $db->getSingleValue($resultBank);
		return $bankInfo;
	}
	/*
	 * Saves new active bank by id
	 * @param <Integer> $bankId - bank id
	 * @return <Boolean> - true on success or false
	 */

	public function setActiveBankById($bankId)
	{
		$db = PearDatabase::getInstance();

		$query = 'UPDATE `yetiforce_currencyupdate_banks` SET `active` = 0;';
		$db->query($query);

		$query = 'UPDATE `yetiforce_currencyupdate_banks` SET `active` = ? WHERE `id` = ? LIMIT 1;';
		$result = $db->pquery($query, [1, $bankId]);

		if ($db->getAffectedRowCount($result)) {
			return true;
		} else {
			return false;
		}
	}
	/*
	 * Returns active banks name
	 * @return <String> - bank name
	 */

	public function getActiveBankName()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT `bank_name` FROM `yetiforce_currencyupdate_banks` WHERE `active` = 1 LIMIT 1;';
		$result = $db->query($query);

		return $db->getSingleValue($result);
	}
}
